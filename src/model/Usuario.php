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

    public static function insertar($usuario, $rol, $id_sucursal, $estado, $id_cliente, $hashed_password, $user_create)
    {
        $conn = Conexion::conectar();
        $date_create = date('Y-m-d H:i:s');
        $sql = "INSERT INTO usuario (usuario, contraseña, rol, user_create, user_update, id_cliente, id_sucursal, estado, date_create, date_update)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiisss", $usuario, $hashed_password, $rol, $user_create, $user_create, $id_cliente, $id_sucursal, $estado, $date_create, $date_create);
        return $stmt->execute();
    }

    public static function actualizar($id, $usuario, $rol, $id_sucursal, $estado, $id_cliente, $hashed_password = null, $user_update)
    {
        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');

        if ($hashed_password) {
            $sql = "UPDATE usuario SET usuario=?, contraseña=?, rol=?, id_sucursal=?, estado=?, id_cliente=?, user_update=?, date_update=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiiissi", $usuario, $hashed_password, $rol, $id_sucursal, $estado, $id_cliente, $user_update, $date_update, $id);
        } else {
            $sql = "UPDATE usuario SET usuario=?, rol=?, id_sucursal=?, estado=?, id_cliente=?, user_update=?, date_update=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiiissi", $usuario, $rol, $id_sucursal, $estado, $id_cliente, $user_update, $date_update, $id);
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

    public static function obtenerSucursalesPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT id, razon_social FROM sucursal WHERE id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function obtenerPaginado($id_cliente, $limit, $offset, $sort, $dir)
    {
        $conn = Conexion::conectar();
        $allowed = ['usuario', 'estado', 'rol', 'sucursal', 'id'];
        $sort = in_array($sort, $allowed) ? $sort : 'sucursal';
        $dir = $dir === 'DESC' ? 'DESC' : 'ASC';

        $sql = "
        SELECT u.id, u.usuario, u.estado, u.rol, s.razon_social AS sucursal, u.id_sucursal
        FROM usuario u
        INNER JOIN sucursal s ON u.id_sucursal = s.id
        WHERE u.id_cliente = ?
        ORDER BY $sort $dir
        LIMIT ? OFFSET ?
    ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $id_cliente, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function contarPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row['total'];
    }
}
