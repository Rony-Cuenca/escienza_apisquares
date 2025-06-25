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
            id_sucursal,
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
            $data['id_sucursal'],
            $data['fecha_registro'],
            $data['estado']
        ]);
        return true;
    }

    public static function existeFecha($fecha, $id_sucursal)
    {
        try {
            $conn = Conexion::conectar();
            $stmt = $conn->prepare("SELECT COUNT(*) FROM resumen_comprobante WHERE fecha_registro = ? AND id_sucursal = ?");
            $stmt->bind_param("si", $fecha, $id_sucursal);
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
}
