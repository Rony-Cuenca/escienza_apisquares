<?php
require_once 'config/conexion.php';

class HomeController
{
    public function index()
    {
        $conn = Conexion::conectar();

        // Obtener sucursales
        $sucursales = [];
        $sqlSuc = "SELECT id, razon_social FROM sucursal WHERE id_cliente = ?";
        $stmt = $conn->prepare($sqlSuc);
        $id_cliente = 1; // Cambia esto según tu lógica
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $sucursales[] = $row;
        }

        // Obtener años disponibles
        $anios = [];
        $sqlAnio = "SELECT DISTINCT YEAR(date_create) as year FROM resumen_comprobante ORDER BY year DESC";
        $result2 = $conn->query($sqlAnio);
        while ($row = $result2->fetch_assoc()) {
            $anios[] = $row['year'];
        }

        $contenido = 'view/components/home.php';
        require 'view/layout.php';
    }

    // Endpoint para AJAX
    public function resumenVentas()
    {
        $conn = Conexion::conectar();
        $sucursal = $_GET['sucursal'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');

        $sql = "SELECT DATE_FORMAT(rc.date_create, '%m') AS mes, tr.descripcion AS tipo, SUM(rc.monto_total) AS total
                FROM resumen_comprobante rc
                JOIN sucursal s ON rc.id_sucursal = s.id
                JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
                WHERE s.id = ? AND YEAR(rc.date_create) = ?
                GROUP BY mes, tipo
                ORDER BY mes, tipo";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $sucursal, $anio);
        $stmt->execute();
        $result = $stmt->get_result();

        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }

        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
}

