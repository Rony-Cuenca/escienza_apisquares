<?php
require_once 'config/conexion.php';

class Usuario {
    public static function obtenerTodos() {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM usuario";
        $res = $conn->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}
