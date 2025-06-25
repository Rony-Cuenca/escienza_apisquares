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
        $sqlAnio = "SELECT DISTINCT YEAR(fecha_registro) as year FROM resumen_comprobante ORDER BY year DESC";
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

        $sql = "SELECT DATE_FORMAT(rc.fecha_registro, '%m') AS mes, tr.descripcion AS tipo, SUM(rc.monto_total) AS total
                FROM resumen_comprobante rc
                JOIN sucursal s ON rc.id_sucursal = s.id
                JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
                WHERE s.id = ? AND YEAR(rc.fecha_registro) = ?
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
                WHERE rc.id_sucursal = ? AND YEAR(rc.fecha_registro) = ? AND MONTH(rc.fecha_registro) = ? AND tr.descripcion = ?
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

    // Endpoint para AJAX - Exoneración IGV por serie
    public function exoneracionIGV()
    {
        // Limpia cualquier salida previa
    if (ob_get_length()) ob_clean();

    header('Content-Type: application/json; charset=utf-8');

    try {
        $conn = Conexion::conectar();
        $sucursal = $_GET['sucursal'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');

        $sql = "SELECT serie, 
                       SUM(suma_exonerada) AS exonerado, 
                       SUM(monto_total) AS total
                FROM resumen_comprobante
                WHERE id_sucursal = ? AND YEAR(fecha_registro) = ?
                GROUP BY serie
                HAVING total > 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $sucursal, $anio);
        $stmt->execute();
        $result = $stmt->get_result();

        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $porcentaje = $row['total'] > 0 ? round(($row['exonerado'] / $row['total']) * 100, 2) : 0;
            $datos[] = [
                'serie' => $row['serie'],
                'porcentaje' => $porcentaje
            ];
        }

        echo json_encode($datos);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
    }

    // Endpoint para variación mensual de ventas
public function variacionVentasMensual()
{
    $conn = Conexion::conectar();
    $sucursal = $_GET['sucursal'] ?? '';
    $anio = $_GET['anio'] ?? date('Y');
    $tipovar = $_GET['tipo'] ?? 'NUBOX360';


    // Suma total de ventas por mes
    $sql = "SELECT DATE_FORMAT(rc.fecha_registro, '%m') AS mes, SUM(rc.monto_total) AS total
        FROM resumen_comprobante rc
        JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
        WHERE rc.id_sucursal = ? AND YEAR(rc.fecha_registro) = ? AND tr.descripcion = ?
        GROUP BY mes
        ORDER BY mes";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $sucursal, $anio , $tipovar);
    $stmt->execute();
    $result = $stmt->get_result();

    $ventas = [];
    while ($row = $result->fetch_assoc()) {
        $ventas[$row['mes']] = floatval($row['total']);
    }

    // Calcula % variación respecto al mes anterior
    $meses = ['01','02','03','04','05','06','07','08','09','10','11','12'];
    $data = [];
    $anterior = null;
    foreach ($meses as $mes) {
        $actual = $ventas[$mes] ?? 0;
        if ($anterior === null) {
            $variacion = null; // Primer mes, sin comparación
        } else {
            $variacion = $anterior == 0 ? null : round((($actual - $anterior) / abs($anterior)) * 100, 2);
        }
        $data[] = [
            'mes' => $mes,
            'total' => $actual,      // <-- AGREGADO
            'variacion' => $variacion
        ];
        $anterior = $actual;
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Endpoint para promedio de venta por comprobante (serie)
public function promedioVentaPorSerie()
{
    $conn = Conexion::conectar();
    $sucursal = $_GET['sucursal'] ?? '';
    $anio = $_GET['anio'] ?? date('Y');
    $mes = $_GET['mes'] ?? date('m');
    $tipo = $_GET['tipo'] ?? 'NUBOX360';

    $sql = "SELECT rc.serie, 
                   SUM(rc.monto_total) AS total_vendido, 
                   SUM(rc.cantidad_compr) AS total_comprobantes,
                   CASE WHEN SUM(rc.cantidad_compr) > 0 THEN ROUND(SUM(rc.monto_total)/SUM(rc.cantidad_compr),2) ELSE 0 END AS promedio
            FROM resumen_comprobante rc
            JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
            WHERE rc.id_sucursal = ? 
              AND YEAR(rc.fecha_registro) = ? 
              AND MONTH(rc.fecha_registro) = ? 
              AND tr.descripcion = ?
            GROUP BY rc.serie
            HAVING total_comprobantes > 0
            ORDER BY promedio DESC";
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

