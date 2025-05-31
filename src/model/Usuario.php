<?php
require_once 'config/conexion.php';

class Usuario
{
    public static function autenticar($usuario, $contrasena)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM usuario WHERE usuario = ? AND contraseña = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $usuario, $contrasena);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            return $user;
        }
        return false;
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

    public static function obtenerPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM usuario WHERE id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
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
        $sql = "UPDATE usuario SET estado = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $estado, $id);
        return $stmt->execute();
    }

    public static function insertar($usuario, $rol, $id_sucursal, $estado, $id_cliente)
    {
        $conn = Conexion::conectar();
        $contraseña = '123456'; // Contraseña por defecto
        $user_create = 'admin';
        $user_update = 'admin';
        $sql = "INSERT INTO usuario (usuario, contraseña, rol, user_create, user_update, id_cliente, id_sucursal, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssii", $usuario, $contraseña, $rol, $user_create, $user_update, $id_cliente, $id_sucursal, $estado);
        return $stmt->execute();
    }

    public static function actualizar($id, $usuario, $rol, $id_sucursal, $estado, $id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "UPDATE usuario SET usuario=?, rol=?, id_sucursal=?, estado=?, id_cliente=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiii", $usuario, $rol, $id_sucursal, $estado, $id_cliente, $id);
        return $stmt->execute();
    }
}
