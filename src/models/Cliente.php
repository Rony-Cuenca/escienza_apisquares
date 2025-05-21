<?php
require_once 'src/config/Conexion.php';

class Cliente {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    public function get_clientes() {
        $stmt = $this->pdo->prepare("SELECT * FROM cliente WHERE estado = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}