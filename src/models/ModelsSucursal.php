<?php
require_once 'src/config/Conexion.php';

class Sucursal {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    public function get_sucursal() {
        $sql = "SELECT * FROM sucursal WHERE estado = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
