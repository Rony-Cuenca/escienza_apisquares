<?php
require_once 'config/conexion.php';

class Cliente
{
    public static function obtenerTodos()
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM cliente ORDER BY razon_social";
        $res = $conn->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function obtenerCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM cliente WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?? null;
    }

    public static function obtenerPorId($id)
    {
        return self::obtenerCliente($id);
    }

    public static function existeRuc($ruc)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as count FROM cliente WHERE ruc = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ruc);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row['count'] > 0;
    }

    public static function crear($ruc, $razon_social, $email = null, $telefono = null, $direccion = null)
    {
        $conn = Conexion::conectar();

        $sql = "INSERT INTO cliente (ruc, razon_social, correo, telefono, direccion, user_create, user_update) 
                VALUES (?, ?, ?, ?, ?, 'sistema', 'sistema')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $ruc, $razon_social, $email, $telefono, $direccion);

        if ($stmt->execute()) {
            return $conn->insert_id;
        }

        return false;
    }

    public static function crearCompleto($ruc, $razon_social, $email = null, $telefono = null, $direccion = null, $departamento = null, $provincia = null, $distrito = null)
    {
        $conn = Conexion::conectar();

        $sql = "INSERT INTO cliente (ruc, razon_social, correo, telefono, direccion, departamento, provincia, distrito, user_create, user_update) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'sistema', 'sistema')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $ruc, $razon_social, $email, $telefono, $direccion, $departamento, $provincia, $distrito);

        if ($stmt->execute()) {
            return $conn->insert_id;
        }

        return false;
    }

    public static function obtenerEstablecimientos($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM establecimiento WHERE id_cliente = ? ORDER BY codigo_establecimiento";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function obtenerClientesConEstablecimientos($limit = 10, $offset = 0, $sort = 'razon_social', $dir = 'ASC')
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
        $clientes = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($clientes as &$cliente) {
            $cliente['establecimientos'] = self::obtenerEstablecimientos($cliente['id']);
        }

        return $clientes;
    }

    public static function actualizar($id, $ruc, $razon_social, $email = null, $telefono = null, $direccion = null, $departamento = null, $provincia = null, $distrito = null)
    {
        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');

        $sql = "UPDATE cliente SET 
                    ruc = ?, razon_social = ?, correo = ?, telefono = ?, direccion = ?, 
                    departamento = ?, provincia = ?, distrito = ?, 
                    user_update = 'sistema', date_update = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssi", $ruc, $razon_social, $email, $telefono, $direccion, $departamento, $provincia, $distrito, $date_update, $id);

        return $stmt->execute();
    }

    public static function cambiarEstado($id, $estado)
    {
        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');

        $sql = "UPDATE cliente SET estado = ?, user_update = 'sistema', date_update = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $estado, $date_update, $id);

        return $stmt->execute();
    }

    public static function existeRucParaEdicion($ruc, $id_excluir)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as count FROM cliente WHERE ruc = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $ruc, $id_excluir);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row['count'] > 0;
    }
}
