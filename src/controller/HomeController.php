<?php
require_once 'config/conexion.php';
require_once 'model/Usuario.php';
require_once 'helpers/sesion_helper.php';

class HomeController
{
    // Función inline para verificar si es SuperAdmin (usa helper)
    private function esSuperAdmin()
    {
        return SesionHelper::esSuperAdmin();
    }

    // Función inline para obtener contexto actual
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

    // Función inline para obtener establecimiento actual (usa helper)
    private function obtenerEstablecimientoActual()
    {
        return SesionHelper::obtenerEstablecimientoActual();
    }

    // Función inline para obtener usuario actual (usa helper)
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

        // Usar SesionHelper para obtener el cliente actual
        $id_cliente = SesionHelper::obtenerClienteActual();
        $establecimientos = Usuario::obtenerEstablecimientosPorCliente($id_cliente);

        // Obtener información del contexto
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
        
        // Usar SesionHelper para obtener datos de manera consistente
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
        $conn = Conexion::conectar();
        $establecimiento = $_GET['establecimiento'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');
        
        // Usar SesionHelper de manera consistente
        $id_cliente = SesionHelper::obtenerClienteActual();
        
        $sql = "SELECT DATE_FORMAT(rc.fecha_registro, '%m') AS mes, 
                       tr.descripcion AS tipo, 
                       SUM(CASE 
                           WHEN rc.monto_total > 1000000 THEN 0  
                           ELSE rc.monto_total 
                       END) AS total,
                       COUNT(CASE WHEN rc.monto_total > 1000000 THEN 1 END) AS registros_anomalos
                FROM resumen_comprobante rc
                JOIN establecimiento e ON rc.id_establecimiento = e.id
                JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
                WHERE e.id_cliente = ? AND YEAR(rc.fecha_registro) = ?
                GROUP BY mes, tipo
                ORDER BY mes, tipo";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_cliente, $anio);
        $stmt->execute();
        $result = $stmt->get_result();

        $datos = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['registros_anomalos'] > 0) {
                error_log("REGISTROS ANÓMALOS DETECTADOS: Cliente=$id_cliente, Mes={$row['mes']}, Tipo={$row['tipo']}, Cantidad={$row['registros_anomalos']}");
            }

            $datos[] = [
                'mes' => $row['mes'],
                'tipo' => $row['tipo'],
                'total' => $row['total']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }

    public function seriesMasVendidas()
    {
        $conn = Conexion::conectar();
        $establecimiento = $_GET['establecimiento'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('m');
        $tipo = $_GET['tipo'] ?? 'NUBOX360';
        
        // Usar SesionHelper de manera consistente
        $id_cliente = SesionHelper::obtenerClienteActual();

        $sql = "SELECT rc.serie, SUM(rc.monto_total) AS total
                FROM resumen_comprobante rc
                JOIN establecimiento e ON rc.id_establecimiento = e.id
                JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
                WHERE e.id_cliente = ? AND YEAR(rc.fecha_registro) = ? AND MONTH(rc.fecha_registro) = ? AND tr.descripcion = ?
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
            
            // Usar SesionHelper de manera consistente
            $id_cliente = SesionHelper::obtenerClienteActual();

            $sql = "SELECT rc.serie, 
                           SUM(rc.suma_exonerada) AS exonerado, 
                           SUM(rc.monto_total) AS total
                    FROM resumen_comprobante rc
                    JOIN establecimiento e ON rc.id_establecimiento = e.id
                    WHERE e.id_cliente = ? AND YEAR(rc.fecha_registro) = ?
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
        $conn = Conexion::conectar();
        $establecimiento = $_GET['establecimiento'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');
        $tipovar = $_GET['tipo'] ?? 'NUBOX360';
        
        // Usar SesionHelper de manera consistente
        $id_cliente = SesionHelper::obtenerClienteActual();
        
        $sql = "SELECT DATE_FORMAT(rc.fecha_registro, '%m') AS mes, SUM(rc.monto_total) AS total
            FROM resumen_comprobante rc
            JOIN establecimiento e ON rc.id_establecimiento = e.id
            JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
            WHERE e.id_cliente = ? AND YEAR(rc.fecha_registro) = ? AND tr.descripcion = ?
            GROUP BY mes
            ORDER BY mes";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $id_cliente, $anio, $tipovar);
        $stmt->execute();
        $result = $stmt->get_result();

        $ventas = [];
        while ($row = $result->fetch_assoc()) {
            $ventas[$row['mes']] = floatval($row['total']);
        }

        $meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $data = [];
        $anterior = null;
        foreach ($meses as $mes) {
            $actual = $ventas[$mes] ?? 0;
            if ($anterior === null) {
                $variacion = null;
            } else {
                $variacion = $anterior == 0 ? null : round((($actual - $anterior) / abs($anterior)) * 100, 2);
            }
            $data[] = [
                'mes' => $mes,
                'total' => $actual,
                'variacion' => $variacion
            ];
            $anterior = $actual;
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function promedioVentaPorSerie()
    {
        $conn = Conexion::conectar();
        $establecimiento = $_GET['establecimiento'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('m');
        $tipo = $_GET['tipo'] ?? 'NUBOX360';
        
        // Usar SesionHelper de manera consistente
        $id_cliente = SesionHelper::obtenerClienteActual();

        $sql = "SELECT rc.serie, 
                       SUM(rc.monto_total) AS total_vendido, 
                       SUM(rc.cantidad_compr) AS total_comprobantes,
                       CASE WHEN SUM(rc.cantidad_compr) > 0 THEN ROUND(SUM(rc.monto_total)/SUM(rc.cantidad_compr),2) ELSE 0 END AS promedio
                FROM resumen_comprobante rc
                JOIN establecimiento e ON rc.id_establecimiento = e.id
                JOIN tipo_reportedoc tr ON rc.id_reporte = tr.id
                WHERE e.id_cliente = ? 
                  AND YEAR(rc.fecha_registro) = ? 
                  AND MONTH(rc.fecha_registro) = ? 
                  AND tr.descripcion = ?
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
