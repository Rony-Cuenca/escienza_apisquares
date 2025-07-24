<?php
require_once 'config/conexion.php';

class VentaGlobal
{
    public static function Insertar($data)
    {
        $conn = Conexion::conectar();

        $sql = "INSERT INTO ventas_globales (
            producto,
            cantidad,
            total,
            user_create,
            user_update,
            id_establecimiento,
            fecha_registro,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['producto'],
            $data['cantidad'],
            $data['total'],
            $data['user_create'],
            $data['user_update'],
            $data['id_establecimiento'],
            $data['fecha_registro'],
            $data['estado']
        ]);
        return true;
    }

    public static function obtenerPorMes($mesSeleccionado, $id_establecimiento)
    {
        $conn = Conexion::conectar();
        if (empty($mesSeleccionado)) {
            $sql = "SELECT 
                        producto,
                        SUM(cantidad) as total_cantidad,
                        SUM(total) as total_importe,
                        COUNT(*) as cantidad_registros
                    FROM ventas_globales 
                    WHERE id_establecimiento = ? 
                    AND estado = 1
                    GROUP BY producto
                    ORDER BY producto";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id_establecimiento);
        } else {
            $sql = "SELECT 
                        producto,
                        SUM(cantidad) as total_cantidad,
                        SUM(total) as total_importe,
                        COUNT(*) as cantidad_registros
                    FROM ventas_globales 
                    WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = ? 
                    AND id_establecimiento = ? 
                    AND estado = 1
                    GROUP BY producto
                    ORDER BY producto";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $mesSeleccionado, $id_establecimiento);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }
        return $datos;
    }

    public static function obtenerTodos()
    {
        $conn = Conexion::conectar();
        $id_establecimiento = $_SESSION['id_establecimiento'] ?? null;

        $sql = "SELECT * FROM ventas_globales 
                WHERE id_establecimiento = ? 
                AND estado = 1 
                ORDER BY date_create DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_establecimiento);
        $stmt->execute();
        $result = $stmt->get_result();

        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }
        return $datos;
    }

    public static function obtenerTotalesGenerales($mesSeleccionado)
    {
        $conn = Conexion::conectar();
        $id_establecimiento = $_SESSION['id_establecimiento'] ?? null;

        $sql = "SELECT 
                    COUNT(*) as total_registros,
                    SUM(cantidad) as suma_cantidad,
                    SUM(total) as suma_total
                FROM ventas_globales 
                WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = ? 
                AND id_establecimiento = ? 
                AND estado = 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $mesSeleccionado, $id_establecimiento);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
