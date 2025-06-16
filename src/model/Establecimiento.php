<?php
require_once 'config/conexion.php';

class Establecimiento
{
    public static function obtenerPorCliente($id_cliente, $limit = 10, $offset = 0)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM sucursal WHERE id_cliente = ? LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $id_cliente, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function contarPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM sucursal WHERE id_cliente = ?";
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
        $sql = "SELECT * FROM sucursal WHERE id = ? AND id_cliente = ?";
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
        $sql = "INSERT INTO sucursal (ruc, razon_social, direccion, id_cliente, estado, user_create, user_update, date_create, date_update)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssisssss",
            $data['ruc'],
            $data['razon_social'],
            $data['direccion'],
            $data['id_cliente'],
            $data['estado'],
            $data['user_create'],
            $data['user_update'],
            $date_create,
            $date_create
        );
        return $stmt->execute();
    }

    public static function actualizar($id, $data)
    {
        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');
        $sql = "UPDATE sucursal SET ruc = ?, razon_social = ?, direccion = ?, estado = ?, user_update = ?, date_update = ? WHERE id = ? AND id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssisssii",
            $data['ruc'],
            $data['razon_social'],
            $data['direccion'],
            $data['estado'],
            $data['user_update'],
            $date_update,
            $id,
            $data['id_cliente']
        );
        return $stmt->execute();
    }

    public static function cambiarEstado($id, $estado, $id_cliente)
    {
        $conn = Conexion::conectar();
        $user_update = $_SESSION['usuario'] ?? 'desconocido';
        $date_update = date('Y-m-d H:i:s');
        $sql = "UPDATE sucursal SET estado = ?, user_update = ?, date_update = ? WHERE id = ? AND id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issii", $estado, $user_update, $date_update, $id, $id_cliente);
        $result = $stmt->execute();

        if ($result) {
            $sqlUsuarios = "UPDATE usuario SET estado = ?, user_update = ?, date_update = ? WHERE id_sucursal = ? AND id_cliente = ?";
            $stmtUsuarios = $conn->prepare($sqlUsuarios);
            $stmtUsuarios->bind_param("issii", $estado, $user_update, $date_update, $id, $id_cliente);
            $stmtUsuarios->execute();
        }

        return $result;
    }

    public static function existeRuc($ruc, $id = 0)
    {
        $conn = Conexion::conectar();
        if ($id > 0) {
            $sql = "SELECT id FROM sucursal WHERE ruc = ? AND id != ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $ruc, $id);
        } else {
            $sql = "SELECT id FROM sucursal WHERE ruc = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $ruc);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ? true : false;
    }
}
