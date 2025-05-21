<?php
require_once 'src/config/Conexion.php';

class ResComprobante {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    public function obtenerTodos() {
        $sql = "SELECT * FROM rescomprobante WHERE estado = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}