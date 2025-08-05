<?php
require_once 'config/conexion.php';
require_once 'model/Usuario.php';
require_once 'model/Cuadre.php';
require_once 'helpers/sesion_helper.php';

class HomeController
{
    private function esSuperAdmin()
    {
        return SesionHelper::esSuperAdmin();
    }

    private function obtenerContextoActual()
    {
        $es_modo_directo = SesionHelper::esModoSuperAdmin();
        $es_superadmin = SesionHelper::esSuperAdmin();

        return [
            'es_modo_directo' => $es_modo_directo,
            'es_superadmin' => $es_superadmin,
            'establecimiento_id' => SesionHelper::obtenerEstablecimientoActual(),
            'usuario_id' => SesionHelper::obtenerUsuarioActual(),
            'cliente_id' => SesionHelper::obtenerClienteActual(),
            'rol' => $_SESSION['rol'] ?? ''
        ];
    }

    private function obtenerEstablecimientoActual()
    {
        return SesionHelper::obtenerEstablecimientoActual();
    }

    private function obtenerUsuarioActual()
    {
        return SesionHelper::obtenerUsuarioActual();
    }

    public function index()
    {
        $conn = Conexion::conectar();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id_cliente = SesionHelper::obtenerClienteActual();
        $establecimientos = Usuario::obtenerEstablecimientosPorCliente($id_cliente);
        $contexto = $this->obtenerContextoActual();

        $anios = [];
        $sqlAnio = "SELECT DISTINCT YEAR(rc.fecha_registro) as year 
                    FROM resumen_comprobante rc
                    JOIN establecimiento e ON rc.id_establecimiento = e.id
                    WHERE e.id_cliente = ?
                    ORDER BY year DESC";
        $stmt = $conn->prepare($sqlAnio);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result2 = $stmt->get_result();
        while ($row = $result2->fetch_assoc()) {
            $anios[] = $row['year'];
        }

        $contenido = 'view/components/home.php';
        require 'view/layout.php';
    }

    public function dashboard()
    {
        $conn = Conexion::conectar();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $contexto = $this->obtenerContextoActual();
        $id_cliente = SesionHelper::obtenerClienteActual();
        $establecimiento_id = SesionHelper::obtenerEstablecimientoActual();
        $establecimientos = Usuario::obtenerEstablecimientosPorCliente($id_cliente);

        $anios = [];
        $sqlAnio = "SELECT DISTINCT YEAR(rc.fecha_registro) as year 
                    FROM resumen_comprobante rc
                    JOIN establecimiento e ON rc.id_establecimiento = e.id
                    WHERE e.id_cliente = ?
                    ORDER BY year DESC";
        $stmt = $conn->prepare($sqlAnio);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result2 = $stmt->get_result();
        while ($row = $result2->fetch_assoc()) {
            $anios[] = $row['year'];
        }

        $contenido = 'view/components/home.php';
        require 'view/layout.php';
    }

    public function resumenVentas()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $anio = $_GET['anio'] ?? date('Y');
            $establecimiento = $_GET['establecimiento'] ?? '';
            
            error_log("resumenVentas llamada - Año: $anio, Establecimiento: $establecimiento");
            
            $conn = Conexion::conectar();
            $id_cliente = SesionHelper::obtenerClienteActual();
            
            if (!$id_cliente) {
                error_log("Error: Cliente no identificado");
                echo json_encode([]);
                exit;
            }
            
            error_log("Cliente identificado: $id_cliente");
            
            $sql = "SELECT DATE_FORMAT(rc.fecha_registro, '%m') AS mes, 
                           tr.descripcion AS tipo, 
                           SUM(rc.monto_total) AS total
                    FROM resumen_comprobante rc
                    INNER JOIN establecimiento e ON rc.id_establecimiento = e.id
                    INNER JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
                    WHERE e.id_cliente = ? 
                      AND YEAR(rc.fecha_registro) = ?
                      AND rc.estado = 1
                      AND tr.estado = 1
                    GROUP BY mes, tipo, tr.id
                    ORDER BY mes ASC, tr.id ASC
                    LIMIT 50";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_cliente, $anio);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $datos = [];
            while ($row = $result->fetch_assoc()) {
                $datos[] = [
                    'mes' => $row['mes'],
                    'tipo' => $row['tipo'],
                    'total' => floatval($row['total'])
                ];
            }
            
            error_log("resumenVentas - Total datos procesados: " . count($datos));
            echo json_encode($datos);
            
        } catch (Exception $e) {
            error_log("Error en resumenVentas: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([]);
        }
        
        exit;
    }

    public function seriesMasVendidas()
    {
        $conn = Conexion::conectar();
        $establecimiento = $_GET['establecimiento'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('m');
        $tipo = $_GET['tipo'] ?? 'NUBOX360';
        $id_cliente = SesionHelper::obtenerClienteActual();

        $sql = "SELECT rc.serie, SUM(rc.monto_total) AS total
                FROM resumen_comprobante rc
                JOIN establecimiento e ON rc.id_establecimiento = e.id
                JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
                WHERE e.id_cliente = ? 
                  AND YEAR(rc.fecha_registro) = ? 
                  AND MONTH(rc.fecha_registro) = ? 
                  AND tr.descripcion = ?
                  AND rc.estado = 1
                GROUP BY rc.serie
                HAVING total > 0
                ORDER BY total DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $id_cliente, $anio, $mes, $tipo);
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

    public function exoneracionIGV()
    {
        if (ob_get_length()) ob_clean();

        header('Content-Type: application/json; charset=utf-8');

        try {
            $conn = Conexion::conectar();
            $establecimiento = $_GET['establecimiento'] ?? '';
            $anio = $_GET['anio'] ?? date('Y');
            $id_cliente = SesionHelper::obtenerClienteActual();

            $sql = "SELECT rc.serie, 
                           SUM(rc.suma_exonerada) AS exonerado, 
                           SUM(rc.monto_total) AS total
                    FROM resumen_comprobante rc
                    JOIN establecimiento e ON rc.id_establecimiento = e.id
                    WHERE e.id_cliente = ? 
                      AND YEAR(rc.fecha_registro) = ?
                      AND rc.estado = 1
                    GROUP BY rc.serie
                    HAVING total > 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_cliente, $anio);
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


    public function variacionVentasMensual()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $anio = $_GET['anio'] ?? date('Y');
            $tipovar = $_GET['tipo'] ?? 'NUBOX360';
            
            $conn = Conexion::conectar();
            $id_cliente = SesionHelper::obtenerClienteActual();
            
            if (!$id_cliente) {
                echo json_encode([]);
                exit;
            }
            
            $idReporte = 1;
            switch ($tipovar) {
                case 'NUBOX360': $idReporte = 1; break;
                case 'SIRE': $idReporte = 2; break;
                case 'EDSUITE': $idReporte = 3; break;
            }
            
            $sql = "SELECT MONTH(rc.fecha_registro) AS mes,
                           SUM(rc.monto_total) AS total
                    FROM resumen_comprobante rc
                    INNER JOIN establecimiento e ON rc.id_establecimiento = e.id
                    WHERE e.id_cliente = ?
                      AND YEAR(rc.fecha_registro) = ?
                      AND rc.id_reporte = ?
                      AND rc.estado = 1
                    GROUP BY mes
                    ORDER BY mes ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $id_cliente, $anio, $idReporte);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $ventas = [];
            while ($row = $result->fetch_assoc()) {
                $ventas[sprintf('%02d', $row['mes'])] = floatval($row['total']);
            }

            $meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
            $data = [];
            $anterior = null;
            
            foreach ($meses as $mes) {
                $actual = $ventas[$mes] ?? 0;
                $variacion = null;
                
                if ($anterior !== null && $anterior > 0) {
                    $variacion = round((($actual - $anterior) / $anterior) * 100, 2);
                } elseif ($anterior !== null && $anterior == 0 && $actual > 0) {
                    $variacion = 100;
                } elseif ($anterior !== null) {
                    $variacion = 0;
                }
                
                $data[] = [
                    'mes' => $mes,
                    'total' => $actual,
                    'variacion' => $variacion
                ];
                $anterior = $actual;
            }

            echo json_encode($data);
            
        } catch (Exception $e) {
            error_log("Error en variacionVentasMensual: " . $e->getMessage());
            echo json_encode([]);
        }
        
        exit;
    }

      public function promedioVentaPorSerie()
    {
        $conn = Conexion::conectar();
        $establecimiento = $_GET['establecimiento'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('m');
        $tipo = $_GET['tipo'] ?? 'NUBOX360';
        $id_cliente = SesionHelper::obtenerClienteActual();

        $sql = "SELECT rc.serie, 
                       SUM(rc.monto_total) AS total_vendido, 
                       SUM(rc.cantidad_compr) AS total_comprobantes,
                       CASE 
                           WHEN SUM(rc.cantidad_compr) > 0 
                           THEN ROUND(SUM(rc.monto_total)/SUM(rc.cantidad_compr),2) 
                           ELSE 0 
                       END AS promedio
                FROM resumen_comprobante rc
                JOIN establecimiento e ON rc.id_establecimiento = e.id
                JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
                WHERE e.id_cliente = ? 
                  AND YEAR(rc.fecha_registro) = ? 
                  AND MONTH(rc.fecha_registro) = ? 
                  AND tr.descripcion = ?
                  AND rc.estado = 1
                GROUP BY rc.serie
                HAVING total_comprobantes > 0
                ORDER BY promedio DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $id_cliente, $anio, $mes, $tipo);
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
