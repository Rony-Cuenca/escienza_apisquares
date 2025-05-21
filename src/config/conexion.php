<?php


class Conexion {
    public static function getConexion() {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=bd_testv1;", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}

