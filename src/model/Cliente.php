<?php
require_once 'config/conexion.php';

class Cliente {
    public static function obtenerTodos() {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM cliente";
        $res = $conn->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }
    public static function obtenerCliente($id_cliente) {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM cliente WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?? null;
    }
}
