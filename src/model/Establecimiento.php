<?php
require_once 'config/conexion.php';

class Establecimiento
{
    // Cambia el estado de todos los establecimientos de un cliente
    public static function cambiarEstadoPorCliente($id_cliente, $estado)
    {
        $conn = Conexion::conectar();
        $user_update = $_SESSION['usuario'] ?? 'desconocido';
        $date_update = date('Y-m-d H:i:s');
        $sql = "UPDATE establecimiento SET estado = ?, user_update = ?, date_update = ? WHERE id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $estado, $user_update, $date_update, $id_cliente);
        $result = $stmt->execute();

        // Opcional: también desactivar usuarios de esos establecimientos
        if ($result) {
            $sqlUsuarios = "UPDATE usuario SET estado = ?, user_update = ?, date_update = ? WHERE id_cliente = ?";
            $stmtUsuarios = $conn->prepare($sqlUsuarios);
            $stmtUsuarios->bind_param("issi", $estado, $user_update, $date_update, $id_cliente);
            $stmtUsuarios->execute();
        }
        return $result;
    }
    public static function obtenerPorCliente($id_cliente, $limit = 10, $offset = 0, $sort = 'codigo_establecimiento', $dir = 'ASC')
    {
        $conn = Conexion::conectar();
        
        // Campos permitidos para ordenamiento
        $allowed = ['codigo_establecimiento', 'tipo_establecimiento', 'etiqueta', 'direccion', 'estado', 'origen'];
        $sort = in_array($sort, $allowed) ? $sort : 'codigo_establecimiento';
        $dir = $dir === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT e.*, c.ruc, c.razon_social 
                FROM establecimiento e 
                INNER JOIN cliente c ON e.id_cliente = c.id 
                WHERE e.id_cliente = ? AND e.estado IN (1, 2, 3)
                ORDER BY e.$sort $dir 
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $id_cliente, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function contarPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM establecimiento WHERE id_cliente = ? AND estado IN (1, 2, 3)";
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
        $sql = "SELECT e.*, c.ruc, c.razon_social 
                FROM establecimiento e 
                INNER JOIN cliente c ON e.id_cliente = c.id 
                WHERE e.id = ? AND e.id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function obtenerEstablecimiento($id)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM establecimiento WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function obtenerEstablecimientoPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT id,etiqueta FROM establecimiento WHERE id_cliente = ? AND estado IN (1, 2, 3)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $establecimientos = [];
        while ($row = $res->fetch_assoc()) {
            $establecimientos[] = $row;
        }
        return $establecimientos;
    }

    public static function actualizarEtiquetaYDireccion($id, $etiqueta, $direccion, $id_cliente, $user_update)
    {
        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');

        $sql = "UPDATE establecimiento SET 
                etiqueta = ?, 
                direccion = ?, 
                user_update = ?, 
                date_update = ? 
                WHERE id = ? AND id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $etiqueta, $direccion, $user_update, $date_update, $id, $id_cliente);

        return $stmt->execute();
    }

    public static function insertar($data)
    {
        $conn = Conexion::conectar();
        $date_create = date('Y-m-d H:i:s');
        $cliente = self::obtenerClientePorId($data['id_cliente']);
        error_log("Verificando cliente para establecimiento: " . var_export($cliente, true));
        if (!$cliente) {
            error_log("Cliente no encontrado para establecimiento: " . $data['id_cliente']);
            return false;
        }

        $sql = "INSERT INTO establecimiento (
                id_cliente, codigo_establecimiento, tipo_establecimiento, 
                etiqueta, direccion, direccion_completa, departamento, provincia, distrito,
                user_create, user_update, date_create, date_update, estado, origen
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'SUNAT')";

        $stmt = $conn->prepare($sql);
        $id_cliente = $data['id_cliente'];
        $codigo_establecimiento = $data['codigo_establecimiento'] ?? '0000';
        $tipo_establecimiento = $data['tipo_establecimiento'] ?? 'MATRIZ';
        $etiqueta = $data['etiqueta'] ?? $cliente['razon_social'] ?? 'Sin etiqueta';
        $direccion = $data['direccion'];
        $direccion_completa = $data['direccion_completa'] ?? $data['direccion'];
        $departamento = $data['departamento'] ?? null;
        $provincia = $data['provincia'] ?? null;
        $distrito = $data['distrito'] ?? null;
        $user_create = $data['user_create'];
        $user_update = $data['user_update'];
        $estado = $data['estado'];
        $stmt->bind_param(
            "issssssssssssi",
            $id_cliente,
            $codigo_establecimiento,
            $tipo_establecimiento,
            $etiqueta,
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

        $res = $stmt->execute();
        if (!$res) {
            error_log("Error MySQL establecimiento: " . $stmt->error);
        } else {
            error_log("Establecimiento creado correctamente para cliente $id_cliente");
        }
        return $res;
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
                WHERE codigo_establecimiento = ? AND id_cliente = ?";
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
                direccion_completa = ?,
                departamento = ?,
                provincia = ?,
                distrito = ?,
                user_update = ?,
                date_update = ?
                WHERE id = ? AND origen = 'SUNAT'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssi",
            $data['tipo_establecimiento'],
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

    public static function existeCodigoEstablecimiento($id_cliente, $codigo, $excluir_id = null)
    {
        $conn = Conexion::conectar();

        if ($excluir_id) {
            $sql = "SELECT COUNT(*) as total FROM establecimiento 
                    WHERE id_cliente = ? AND codigo_establecimiento = ? AND id != ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $id_cliente, $codigo, $excluir_id);
        } else {
            $sql = "SELECT COUNT(*) as total FROM establecimiento 
                    WHERE id_cliente = ? AND codigo_establecimiento = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $id_cliente, $codigo);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'];

        return $count > 0;
    }

    public static function crearEstablecimientoManual($datos)
    {
        $conn = Conexion::conectar();
        $date_create = date('Y-m-d H:i:s');
        $sqlCheck = "SELECT COUNT(*) as total FROM establecimiento 
                     WHERE id_cliente = ? AND codigo_establecimiento = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("is", $datos['id_cliente'], $datos['codigo_establecimiento']);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        $count = $result->fetch_assoc()['total'];

        if ($count > 0) {
            return ['success' => false, 'error' => 'Ya existe un establecimiento con ese código'];
        }

        $sql = "INSERT INTO establecimiento (
                    id_cliente, codigo_establecimiento, tipo_establecimiento, 
                    etiqueta, direccion, direccion_completa, 
                    departamento, provincia, distrito, 
                    user_create, user_update, date_create, date_update, 
                    estado, origen
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'MANUAL')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssssssssss",
            $datos['id_cliente'],
            $datos['codigo_establecimiento'],
            $datos['tipo_establecimiento'],
            $datos['etiqueta'],
            $datos['direccion'],
            $datos['direccion_completa'],
            $datos['departamento'],
            $datos['provincia'],
            $datos['distrito'],
            $datos['user_create'],
            $datos['user_update'],
            $date_create,
            $date_create
        );

        if ($stmt->execute()) {
            return ['success' => true, 'id' => $conn->insert_id];
        } else {
            return ['success' => false, 'error' => 'Error al crear el establecimiento'];
        }
    }

    public static function actualizar_codigo_establecimiento($id, $codigo_establecimiento)
    {
        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');

        $sql = "UPDATE establecimiento SET 
                    codigo_establecimiento = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ss",
            $codigo_establecimiento,
            $id
        );

        return $stmt->execute();
    }

    public static function obtenerClientePorestablecimiento($id_establecimiento)
    {
        $conn = Conexion::conectar();

        $sql = "
            SELECT c.id, c.ruc
            FROM cliente c
            JOIN establecimiento e ON c.id = e.id_cliente
            WHERE e.id = ?
        ";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_establecimiento);
        $stmt->execute();
        $res = $stmt->get_result();
    
        return $res->fetch_assoc();
    }

}
