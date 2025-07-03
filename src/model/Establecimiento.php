<?php
require_once 'config/conexion.php';

class Establecimiento
{
    public static function obtenerPorCliente($id_cliente, $limit = 10, $offset = 0)
    {
        $conn = Conexion::conectar();
        
        // Primero verificar si existen establecimientos activos para este cliente
        $sqlCount = "SELECT COUNT(*) as total FROM establecimiento WHERE id_cliente = ? AND estado IN (1, 2)";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bind_param("i", $id_cliente);
        $stmtCount->execute();
        $resultCount = $stmtCount->get_result();
        $count = $resultCount->fetch_assoc()['total'];
        
        // Si no hay establecimientos activos, crear el principal automáticamente
        if ($count == 0) {
            self::crearEstablecimientoPrincipal($id_cliente);
        }
        
        $sql = "SELECT e.*, c.ruc, c.razon_social 
                FROM establecimiento e 
                INNER JOIN cliente c ON e.id_cliente = c.id 
                WHERE e.id_cliente = ? AND e.estado IN (1, 2)
                ORDER BY e.codigo_establecimiento ASC 
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $id_cliente, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    private static function crearEstablecimientoPrincipal($id_cliente)
    {
        $conn = Conexion::conectar();
        
        // Obtener datos del cliente
        $cliente = self::obtenerClientePorId($id_cliente);
        
        if (!$cliente) {
            return false;
        }
        
        $date_create = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO establecimiento (
                    id_cliente, codigo_establecimiento, tipo_establecimiento, 
                    direccion, direccion_completa, departamento, provincia, distrito,
                    user_create, user_update, date_create, date_update, estado
                ) VALUES (?, '0000', 'MATRIZ', ?, ?, ?, ?, ?, 'sistema', 'sistema', ?, ?, 1)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssssss",
            $id_cliente,
            $cliente['direccion'] ?? 'Sin dirección',
            $cliente['direccion'] ?? 'Sin dirección',
            $cliente['departamento'] ?? '',
            $cliente['provincia'] ?? '',
            $cliente['distrito'] ?? '',
            $date_create,
            $date_create
        );
        
        return $stmt->execute();
    }

    public static function contarPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM establecimiento WHERE id_cliente = ? AND estado IN (1, 2)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row['total'];
    }

    public static function obtenerPorId($id, $id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT e.*, c.ruc as ruc_cliente, c.razon_social as razon_social_cliente 
                FROM establecimiento e 
                INNER JOIN cliente c ON e.id_cliente = c.id 
                WHERE e.id = ? AND e.id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function insertar($data)
    {
        $conn = Conexion::conectar();
        $date_create = date('Y-m-d H:i:s');
        
        // Obtener información del cliente (empresa principal)
        $cliente = self::obtenerClientePorId($data['id_cliente']);
        if (!$cliente) {
            return false;
        }
        
        $sql = "INSERT INTO establecimiento (
                    id_cliente, codigo_establecimiento, tipo_establecimiento, 
                    direccion, direccion_completa, departamento, provincia, distrito,
                    user_create, user_update, date_create, date_update, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        // Asignar valores a variables para bind_param
        $id_cliente = $data['id_cliente'];
        $codigo_establecimiento = $data['codigo_establecimiento'] ?? '0000';
        $tipo_establecimiento = $data['tipo_establecimiento'] ?? 'MATRIZ';
        $direccion = $data['direccion'];
        $direccion_completa = $data['direccion_completa'] ?? $data['direccion'];
        $departamento = $data['departamento'] ?? null;
        $provincia = $data['provincia'] ?? null;
        $distrito = $data['distrito'] ?? null;
        $user_create = $data['user_create'];
        $user_update = $data['user_update'];
        $estado = $data['estado'];
        
        $stmt->bind_param(
            "isssssssssssi",
            $id_cliente,
            $codigo_establecimiento,
            $tipo_establecimiento,
            $direccion,
            $direccion_completa,
            $departamento,
            $provincia,
            $distrito,
            $user_create,
            $user_update,
            $date_create,
            $date_create,
            $estado
        );
        
        return $stmt->execute();
    }

    public static function actualizar($id, $data)
    {
        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');
        
        $sql = "UPDATE establecimiento SET 
                    codigo_establecimiento = ?, tipo_establecimiento = ?, 
                    direccion = ?, direccion_completa = ?, 
                    departamento = ?, provincia = ?, distrito = ?,
                    estado = ?, user_update = ?, date_update = ? 
                WHERE id = ? AND id_cliente = ?";
        
        $stmt = $conn->prepare($sql);
        
        // Asignar valores a variables para bind_param
        $codigo_establecimiento = $data['codigo_establecimiento'] ?? '0000';
        $tipo_establecimiento = $data['tipo_establecimiento'] ?? 'MATRIZ';
        $direccion = $data['direccion'];
        $direccion_completa = $data['direccion_completa'] ?? $data['direccion'];
        $departamento = $data['departamento'] ?? null;
        $provincia = $data['provincia'] ?? null;
        $distrito = $data['distrito'] ?? null;
        $estado = $data['estado'];
        $user_update = $data['user_update'];
        $id_cliente = $data['id_cliente'];
        
        $stmt->bind_param(
            "sssssssissii",
            $codigo_establecimiento,
            $tipo_establecimiento,
            $direccion,
            $direccion_completa,
            $departamento,
            $provincia,
            $distrito,
            $estado,
            $user_update,
            $date_update,
            $id,
            $id_cliente
        );
        
        return $stmt->execute();
    }

    public static function cambiarEstado($id, $estado, $id_cliente)
    {
        $conn = Conexion::conectar();
        $user_update = $_SESSION['usuario'] ?? 'desconocido';
        $date_update = date('Y-m-d H:i:s');
        
        $sql = "UPDATE establecimiento SET estado = ?, user_update = ?, date_update = ? WHERE id = ? AND id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issii", $estado, $user_update, $date_update, $id, $id_cliente);
        $result = $stmt->execute();

        if ($result) {
            // Actualizar usuarios relacionados al establecimiento
            $sqlUsuarios = "UPDATE usuario SET estado = ?, user_update = ?, date_update = ? WHERE id_establecimiento = ? AND id_cliente = ?";
            $stmtUsuarios = $conn->prepare($sqlUsuarios);
            $stmtUsuarios->bind_param("issii", $estado, $user_update, $date_update, $id, $id_cliente);
            $stmtUsuarios->execute();
        }

        return $result;
    }

    public static function existeRucEnCliente($ruc)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT id FROM cliente WHERE ruc = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ruc);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ? true : false;
    }

    public static function existeRuc($ruc, $id = 0)
    {
        // Mantenemos esta función para compatibilidad, pero ahora verifica en clientes
        return self::existeRucEnCliente($ruc);
    }

    public static function obtenerClientePorId($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM cliente WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function obtenerClientePorRuc($ruc)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM cliente WHERE ruc = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ruc);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function crearCliente($data)
    {
        $conn = Conexion::conectar();
        $date_create = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO cliente (
                    ruc, razon_social, tipo_contribuyente, condicion, estado_sunat,
                    direccion, departamento, provincia, distrito, ubigeo_sunat,
                    user_create, user_update, date_create, date_update, estado, fecha_consulta_api
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssssssii",
            $data['ruc'],
            $data['razon_social'],
            $data['tipo_contribuyente'] ?? null,
            $data['condicion'] ?? null,
            $data['estado_sunat'] ?? null,
            $data['direccion'],
            $data['departamento'] ?? null,
            $data['provincia'] ?? null,
            $data['distrito'] ?? null,
            $data['ubigeo_sunat'] ?? null,
            $data['user_create'],
            $data['user_update'],
            $date_create,
            $date_create,
            $data['estado'] ?? 1,
            $date_create
        );
        
        if ($stmt->execute()) {
            return $conn->insert_id;
        }
        
        return false;
    }

    public static function obtenerPorCodigoYCliente($codigo, $id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM establecimiento 
                WHERE codigo_establecimiento = ? AND id_cliente = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $codigo, $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function actualizarPorCodigo($id, $data)
    {
        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');
        
        $sql = "UPDATE establecimiento SET 
                    tipo_establecimiento = ?, 
                    direccion = ?, direccion_completa = ?, 
                    departamento = ?, provincia = ?, distrito = ?,
                    user_update = ?, date_update = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssi",
            $data['tipo_establecimiento'],
            $data['direccion'],
            $data['direccion_completa'],
            $data['departamento'],
            $data['provincia'],
            $data['distrito'],
            $data['user_update'],
            $date_update,
            $id
        );
        
        return $stmt->execute();
    }
}
