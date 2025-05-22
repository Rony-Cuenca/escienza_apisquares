<?php
require_once 'src/config/Conexion.php';

class ReporteDoc {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    public function get_repotedoc() {
        $sql = "SELECT * FROM reporte_doc WHERE estado = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

