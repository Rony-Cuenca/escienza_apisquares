<?php
require_once 'config/conexion.php';

class Usuario
{

    public static function autenticar($usuario, $contrasena)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM usuario WHERE usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user && password_verify($contrasena, $user['contraseña']) && $user['estado'] == 1) {
            return $user;
        }
        return false;
    }

    // Verificar si es Super Admin
    public static function esSuperAdmin($usuario_id = null)
    {
        if ($usuario_id === null && isset($_SESSION['id_usuario'])) {
            $usuario_id = $_SESSION['id_usuario'];
        }
        
        if (!$usuario_id) return false;
        
        $conn = Conexion::conectar();
        $sql = "SELECT id FROM usuario WHERE id = ? AND (rol = 'SuperAdmin' OR (id_cliente = 9999 AND rol = 'SuperAdmin')) AND estado = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Crear Super Admin (solo uso manual o migración)
    public static function crearSuperAdmin($usuario, $correo, $contrasena)
    {
        $conn = Conexion::conectar();
        $date_create = date('Y-m-d H:i:s');
        $hashed_password = password_hash($contrasena, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO usuario (usuario, correo, contraseña, rol, user_create, user_update, id_cliente, id_establecimiento, estado, date_create, date_update)
        VALUES (?, ?, ?, 'SuperAdmin', 'system', 'system', 0, 0, 1, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $usuario, $correo, $hashed_password, $date_create, $date_create);
        
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                return false;
            }
            throw $e;
        }
    }

    public static function insertar($usuario, $correo, $rol, $id_establecimiento, $estado, $id_cliente, $hashed_password, $user_create)
    {
        $conn = Conexion::conectar();
        $date_create = date('Y-m-d H:i:s');
        $sql = "INSERT INTO usuario (usuario, correo, contraseña, rol, user_create, user_update, id_cliente, id_establecimiento, estado, date_create, date_update)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssiisss", $usuario, $correo, $hashed_password, $rol, $user_create, $user_create, $id_cliente, $id_establecimiento, $estado, $date_create, $date_create);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                return false;
            }
            throw $e;
        }
    }

    public static function actualizar($id, $usuario, $correo, $rol, $id_establecimiento, $estado, $id_cliente, $user_update, $hashed_password = null)
    {
        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');

        if ($hashed_password) {
            $sql = "UPDATE usuario SET usuario=?, correo=?, contraseña=?, rol=?, id_establecimiento=?, estado=?, id_cliente=?, user_update=?, date_update=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiissi", $usuario, $correo, $hashed_password, $rol, $id_establecimiento, $estado, $id_cliente, $user_update, $date_update, $id);
        } else {
            $sql = "UPDATE usuario SET usuario=?, correo=?, rol=?, id_establecimiento=?, estado=?, id_cliente=?, user_update=?, date_update=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiiissi", $usuario, $correo, $rol, $id_establecimiento, $estado, $id_cliente, $user_update, $date_update, $id);
        }
        return $stmt->execute();
    }

    public static function obtenerId($id)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM usuario WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function cambiarEstado($id, $estado)
    {
        $conn = Conexion::conectar();
        $sql = "UPDATE usuario SET estado = ?, user_update = ?, date_update = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        $user_update = $_SESSION['usuario'] ?? 'desconocido';
        $date_update = date('Y-m-d H:i:s');

        $stmt->bind_param("issi", $estado, $user_update, $date_update, $id);
        return $stmt->execute();
    }

    public static function obtenerCorreoCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT correo FROM cliente WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row['correo'] ?? null;
    }

    public static function obtenerEstablecimientosPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT e.id, e.codigo_establecimiento, e.tipo_establecimiento, e.direccion, 
                       e.etiqueta, c.razon_social, c.ruc 
                FROM establecimiento e 
                INNER JOIN cliente c ON e.id_cliente = c.id 
                WHERE e.id_cliente = ? AND e.estado = 1 
                ORDER BY e.codigo_establecimiento ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function obtenerNombreEstablecimiento($id_establecimiento)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT CONCAT(e.etiqueta, ' (', e.codigo_establecimiento, ')') AS nombre_completo
                FROM establecimiento e 
                INNER JOIN cliente c ON e.id_cliente = c.id 
                WHERE e.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_establecimiento);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row['nombre_completo'] ?? null;
    }

    public static function obtenerPaginadoPorEstablecimiento($id_establecimiento, $limit, $offset, $sort, $dir)
    {
        $conn = Conexion::conectar();
        $allowed = ['usuario', 'correo', 'estado', 'rol', 'establecimiento', 'id'];
        $sort = in_array($sort, $allowed) ? $sort : 'establecimiento';
        $dir = $dir === 'DESC' ? 'DESC' : 'ASC';
        $sql = "
        SELECT u.id, u.usuario, u.correo, u.estado, u.rol, 
               CONCAT(e.etiqueta, ' (', e.codigo_establecimiento, ')') AS establecimiento, 
               u.id_establecimiento
        FROM usuario u
        INNER JOIN establecimiento e ON u.id_establecimiento = e.id
        INNER JOIN cliente c ON e.id_cliente = c.id
        WHERE u.id_establecimiento = ?
        ORDER BY $sort $dir
        LIMIT ? OFFSET ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $id_establecimiento, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function contarPorEstablecimiento($id_establecimiento)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE id_establecimiento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_establecimiento);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row['total'];
    }

    public static function existeUsuario($usuario, $id_usuario = 0)
    {
        $conn = Conexion::conectar();
        if ($id_usuario > 0) {
            $sql = "SELECT id FROM usuario WHERE usuario = ? AND id != ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $usuario, $id_usuario);
        } else {
            $sql = "SELECT id FROM usuario WHERE usuario = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $usuario);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ? true : false;
    }

    public static function existeCorreo($correo, $id_usuario = 0)
    {
        $conn = Conexion::conectar();
        if ($id_usuario > 0) {
            $sql = "SELECT id FROM usuario WHERE correo = ? AND id != ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $correo, $id_usuario);
        } else {
            $sql = "SELECT id FROM usuario WHERE correo = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $correo);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ? true : false;
    }

    // Obtener todos los clientes (solo para Super Admin)
    public static function obtenerTodosLosClientes($limit = 10, $offset = 0, $sort = 'razon_social', $dir = 'ASC')
    {
        $conn = Conexion::conectar();
        $validSorts = ['id', 'razon_social', 'ruc', 'telefono', 'correo', 'estado'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'razon_social';
        }
        
        $sql = "SELECT * FROM cliente ORDER BY $sort $dir LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Contar todos los clientes
    public static function contarTodosLosClientes()
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM cliente";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
