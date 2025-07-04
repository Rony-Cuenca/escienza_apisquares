<?php
require_once 'config/conexion.php';

class Cuadre
{

    public static function Insertar($data)
    {
        $conn = Conexion::conectar();

        $sql = "INSERT INTO resumen_comprobante (
            serie,
            cantidad_compr,
            suma_gravada,
            suma_exonerada,
            suma_inafecto,
            suma_igv,
            monto_total,
            tipo_comprobante,
            id_reporte,
            user_create,
            user_update,
            id_establecimiento,
            fecha_registro,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['serie'],
            $data['cantidad_compr'],
            $data['suma_gravada'],
            $data['suma_exonerada'],
            $data['suma_inafecto'],
            $data['suma_igv'],
            $data['monto_total'],
            $data['tipo_comprobante'],
            $data['id_reporte'],
            $data['user_create'],
            $data['user_update'],
            $data['id_establecimiento'],
            $data['fecha_registro'],
            $data['estado']
        ]);
        return true;
    }

    public static function existeFecha($fecha, $id_establecimiento)
    {
        try {
            $conn = Conexion::conectar();
            $stmt = $conn->prepare("SELECT COUNT(*) FROM resumen_comprobante WHERE fecha_registro = ? AND id_establecimiento = ?");
            $stmt->bind_param("si", $fecha, $id_establecimiento);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            return $count > 0;
        } catch (Exception $e) {
            throw new Exception("Error al verificar la fecha: " . $e->getMessage());
        }
    }

    public static function obtenerMesesDisponibles()
    {
        $conn = Conexion::conectar();
        $sql = "SELECT DISTINCT DATE_FORMAT(fecha_registro, '%Y-%m') as mes, DATE_FORMAT(fecha_registro, '%M %Y') as mes_nombre FROM resumen_comprobante ORDER BY mes DESC";
        $stmt = $conn->query($sql);
        $meses = [];
        while ($row = $stmt->fetch_assoc()) {
            $meses[] = $row;
        }
        return $meses;
    }

    public static function obtenerCuadresPorMes($mes)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM resumen_comprobante WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = ? ORDER BY fecha_registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        $cuadres = [];
        while ($row = $result->fetch_assoc()) {
            $cuadres[] = $row;
        }
        return $cuadres;
    }

    public static function obtenerTotalesPorTipoComprobante($mes)
    {
        $conn = Conexion::conectar();
        $sql = "
        SELECT tipo_comprobante, id_reporte, SUM(monto_total) as total
        FROM resumen_comprobante
        WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = ?
        GROUP BY tipo_comprobante, id_reporte
    ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        $totales = [];
        while ($row = $result->fetch_assoc()) {
            $totales[$row['tipo_comprobante']][$row['id_reporte']] = $row['total'];
        }
        return $totales;
    }

    public static function obtenerTotalesPorSerie($mes)
    {
        $conn = Conexion::conectar();
        $sql = "
        SELECT 
            serie,
            id_reporte,
            SUM(monto_total) as total
        FROM resumen_comprobante
        WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = ?
        GROUP BY serie, id_reporte
    ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        $series = [];
        while ($row = $result->fetch_assoc()) {
            $series[$row['serie']][$row['id_reporte']] = $row['total'];
        }
        return $series;
    }

    public static function obtenerResumenComprobantes($mes)
    {
        $conn = Conexion::conectar();
        $sql = "
            SELECT 
                serie,
                tipo_comprobante,
                SUM(cantidad_compr) as cantidad_comprobantes,
                SUM(suma_gravada) as suma_gravada,
                SUM(suma_exonerada) as suma_exonerada,
                SUM(suma_inafecto) as suma_inafecto,
                SUM(suma_igv) as suma_igv,
                SUM(monto_total) as monto_total
            FROM resumen_comprobante 
            WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = ?
            AND id_reporte = 3
            GROUP BY serie, tipo_comprobante
            ORDER BY serie, tipo_comprobante
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $resumen = [];
        while ($row = $result->fetch_assoc()) {
            $resumen[] = [
                'serie' => $row['serie'],
                'tipo_comprobante' => $row['tipo_comprobante'],
                'cantidad_comprobantes' => $row['cantidad_comprobantes'],
                'suma_gravada' => $row['suma_gravada'],
                'suma_exonerada' => $row['suma_exonerada'],
                'suma_inafecto' => $row['suma_inafecto'],
                'suma_igv' => $row['suma_igv'],
                'monto_total' => $row['monto_total'],
                'diferencia' => 0 // Se puede calcular según requerimientos específicos
            ];
        }
        
        return $resumen;
    }

    public static function obtenerTotalesPorTipoComprobanteExcluyendoAjenas($mes)
    {
        $conn = Conexion::conectar();
        $id_establecimiento = $_SESSION['id_establecimiento'] ?? null;
        
        $sql = "
        SELECT tipo_comprobante, id_reporte, SUM(monto_total) as total
        FROM resumen_comprobante rc
        WHERE DATE_FORMAT(rc.fecha_registro, '%Y-%m') = ?
        AND rc.id_establecimiento = ?
        AND rc.serie NOT IN (
            SELECT DISTINCT serie 
            FROM series_ajenas sa 
            WHERE sa.id_establecimiento = rc.id_establecimiento 
            AND sa.estado = 1
            AND DATE_FORMAT(sa.fecha_registro, '%Y-%m') = ?
        )
        GROUP BY tipo_comprobante, id_reporte
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $mes, $id_establecimiento, $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        $totales = [];
        while ($row = $result->fetch_assoc()) {
            $totales[$row['tipo_comprobante']][$row['id_reporte']] = $row['total'];
        }
        return $totales;
    }

    public static function obtenerTotalesPorSerieExcluyendoAjenas($mes)
    {
        $conn = Conexion::conectar();
        $id_establecimiento = $_SESSION['id_establecimiento'] ?? null;
        
        $sql = "
        SELECT 
            rc.serie,
            rc.id_reporte,
            SUM(rc.monto_total) as total
        FROM resumen_comprobante rc
        WHERE DATE_FORMAT(rc.fecha_registro, '%Y-%m') = ?
        AND rc.id_establecimiento = ?
        AND rc.serie NOT IN (
            SELECT DISTINCT serie 
            FROM series_ajenas sa 
            WHERE sa.id_establecimiento = rc.id_establecimiento 
            AND sa.estado = 1
            AND DATE_FORMAT(sa.fecha_registro, '%Y-%m') = ?
        )
        GROUP BY rc.serie, rc.id_reporte
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $mes, $id_establecimiento, $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        $series = [];
        while ($row = $result->fetch_assoc()) {
            $series[$row['serie']][$row['id_reporte']] = $row['total'];
        }
        return $series;
    }
}
