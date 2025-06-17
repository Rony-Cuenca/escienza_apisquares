<?php
require_once 'config/conexion.php';
require_once 'model/Usuario.php';

class HomeController
{
    public function index()
    {
        $conn = Conexion::conectar();
       if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $id_cliente = $_SESSION['id_cliente'] ?? 1;
   // echo "ID CLIENTE: $id_cliente";

    // Obtener sucursales usando el modelo Usuario
    $sucursales = Usuario::obtenerSucursalesPorCliente($id_cliente);

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

    // Endpoint para AJAX - Series más vendidas por mes y sucursal
    public function seriesMasVendidas()
    {
        $conn = Conexion::conectar();
        $sucursal = $_GET['sucursal'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('m');
        $tipo = $_GET['tipo'] ?? 'NUBOX360';

        $sql = "SELECT rc.serie, SUM(rc.monto_total) AS total
                FROM resumen_comprobante rc
                JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
                WHERE rc.id_sucursal = ? AND YEAR(rc.date_create) = ? AND MONTH(rc.date_create) = ? AND tr.descripcion = ?
                GROUP BY rc.serie
                HAVING total > 0
                ORDER BY total DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $sucursal, $anio, $mes, $tipo);
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

