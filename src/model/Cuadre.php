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
        $id_cliente = $_SESSION['id_cliente'] ?? null;

        $sql = "SELECT DISTINCT DATE_FORMAT(rc.fecha_registro, '%Y-%m') as mes, DATE_FORMAT(rc.fecha_registro, '%M %Y') as mes_nombre 
                FROM resumen_comprobante rc
                INNER JOIN establecimiento e ON rc.id_establecimiento = e.id
                WHERE e.id_cliente = ? 
                ORDER BY mes DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();

        $meses = [];
        while ($row = $result->fetch_assoc()) {
            $meses[] = $row;
        }
        return $meses;
    }

    public static function obtenerCuadresPorMes($mes)
    {
        $conn = Conexion::conectar();
        $id_cliente = $_SESSION['id_cliente'] ?? null;

        $sql = "SELECT rc.* FROM resumen_comprobante rc
                INNER JOIN establecimiento e ON rc.id_establecimiento = e.id
                WHERE DATE_FORMAT(rc.fecha_registro, '%Y-%m') = ? 
                AND e.id_cliente = ? 
                ORDER BY rc.fecha_registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $mes, $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $cuadres = [];
        while ($row = $result->fetch_assoc()) {
            $cuadres[] = $row;
        }
        return $cuadres;
    }

    public static function obtenerTotalesPorTipoComprobante($mes, $id_establecimiento = null)
    {
        $conn = Conexion::conectar();
        $id_cliente = $_SESSION['id_cliente'] ?? null;

        $sql = "
        SELECT rc.tipo_comprobante, rc.id_reporte, SUM(rc.monto_total) as total
        FROM resumen_comprobante rc
        INNER JOIN establecimiento e ON rc.id_establecimiento = e.id
        WHERE DATE_FORMAT(rc.fecha_registro, '%Y-%m') = ?
        AND e.id_cliente = ?
        ";
        if ($id_establecimiento) {
            $sql .= " AND rc.id_establecimiento = ?";
        }
        $sql .= " GROUP BY rc.tipo_comprobante, rc.id_reporte";

        if ($id_establecimiento) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $mes, $id_cliente, $id_establecimiento);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $mes, $id_cliente);
        }
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
        $id_cliente = $_SESSION['id_cliente'] ?? null;

        $sql = "
        SELECT 
            rc.serie,
            rc.id_reporte,
            SUM(rc.monto_total) as total
        FROM resumen_comprobante rc
        INNER JOIN establecimiento e ON rc.id_establecimiento = e.id
        WHERE DATE_FORMAT(rc.fecha_registro, '%Y-%m') = ?
        AND e.id_cliente = ?
        GROUP BY rc.serie, rc.id_reporte
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $mes, $id_cliente);
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
        $id_cliente = $_SESSION['id_cliente'] ?? null;

        $sql = "
            SELECT 
                rc.serie,
                rc.tipo_comprobante,
                SUM(rc.cantidad_compr) as cantidad_comprobantes,
                SUM(rc.suma_gravada) as suma_gravada,
                SUM(rc.suma_exonerada) as suma_exonerada,
                SUM(rc.suma_inafecto) as suma_inafecto,
                SUM(rc.suma_igv) as suma_igv,
                SUM(rc.monto_total) as monto_total
            FROM resumen_comprobante rc
            INNER JOIN establecimiento e ON rc.id_establecimiento = e.id
            WHERE DATE_FORMAT(rc.fecha_registro, '%Y-%m') = ?
            AND e.id_cliente = ?
            AND rc.id_reporte = 3
            GROUP BY rc.serie, rc.tipo_comprobante
            ORDER BY rc.serie, rc.tipo_comprobante
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $mes, $id_cliente);
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
                'diferencia' => 0
            ];
        }

        return $resumen;
    }

    public static function obtenerTotalesPorTipoComprobanteExcluyendoAjenas($mes)
    {
        $conn = Conexion::conectar();
        $id_cliente = $_SESSION['id_cliente'] ?? null;

        $sql = "
        SELECT rc.tipo_comprobante, rc.id_reporte, SUM(rc.monto_total) as total
        FROM resumen_comprobante rc
        INNER JOIN establecimiento e ON rc.id_establecimiento = e.id
        WHERE DATE_FORMAT(rc.fecha_registro, '%Y-%m') = ?
        AND e.id_cliente = ?
        AND rc.serie NOT IN (
            SELECT DISTINCT sa.serie 
            FROM series_ajenas sa 
            INNER JOIN establecimiento e2 ON sa.id_establecimiento = e2.id
            WHERE e2.id_cliente = ?
            AND sa.estado = 1
            AND DATE_FORMAT(sa.fecha_registro, '%Y-%m') = ?
        )
        GROUP BY rc.tipo_comprobante, rc.id_reporte
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siis", $mes, $id_cliente, $id_cliente, $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        $totales = [];
        while ($row = $result->fetch_assoc()) {
            $totales[$row['tipo_comprobante']][$row['id_reporte']] = $row['total'];
        }
        return $totales;
    }

    public static function obtenerTotalesPorSerieExcluyendoAjenas($mes, $id_establecimiento = null)
    {
        $conn = Conexion::conectar();
        $id_cliente = $_SESSION['id_cliente'] ?? null;

        $sql = "
        SELECT 
            rc.serie,
            rc.id_reporte,
            SUM(rc.monto_total) as total
        FROM resumen_comprobante rc
        INNER JOIN establecimiento e ON rc.id_establecimiento = e.id
        WHERE DATE_FORMAT(rc.fecha_registro, '%Y-%m') = ?
        AND e.id_cliente = ?
        AND rc.serie NOT IN (
            SELECT DISTINCT sa.serie 
            FROM series_ajenas sa 
            INNER JOIN establecimiento e2 ON sa.id_establecimiento = e2.id
            WHERE e2.id_cliente = ?
            AND sa.estado = 1
            AND DATE_FORMAT(sa.fecha_registro, '%Y-%m') = ?
        )
        ";
        if ($id_establecimiento) {
            $sql .= " AND rc.id_establecimiento = ?";
        }
        $sql .= " GROUP BY rc.serie, rc.id_reporte";

        if ($id_establecimiento) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siisi", $mes, $id_cliente, $id_cliente, $mes, $id_establecimiento);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siis", $mes, $id_cliente, $id_cliente, $mes);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $series = [];
        while ($row = $result->fetch_assoc()) {
            $series[$row['serie']][$row['id_reporte']] = $row['total'];
        }
        return $series;
    }

    public static function datosEstablecimiento($id_establecimiento, $mes)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM resumen_comprobante WHERE id_establecimiento = ? AND DATE_FORMAT(fecha_registro, '%Y-%m') = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_establecimiento, $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return ($row && $row['total'] > 0);
    }
}
