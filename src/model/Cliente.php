<?php
require_once 'config/conexion.php';

class Cliente {
    public static function obtenerTodos() {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM cliente";
        $res = $conn->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}
