<?php
class Conexion {
    public static function conectar() {
        $conn = new mysqli('localhost', 'root', '', 'db_apisquares');
        if ($conn->connect_error) {
            die("Error de conexión: ". $conn->connect_error);
        }
        return $conn;
    }
}