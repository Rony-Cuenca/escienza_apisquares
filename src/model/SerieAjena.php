<?php
require_once 'config/conexion.php';

class SerieAjena
{
    public static function Insertar($data)
    {
        $conn = Conexion::conectar();

        $sql = "INSERT INTO series_ajenas (
            serie,
            conteo,
            total,
            user_create,
            user_update,
            id_establecimiento,
            fecha_registro,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['serie'],
            $data['conteo'],
            $data['total'],
            $data['user_create'],
            $data['user_update'],
            $data['id_establecimiento'],
            $data['fecha_registro'],
            $data['estado']
        ]);
        return true;
    }

    public static function obtenerPorMes($mesSeleccionado, $idEstablecimiento)
    {
        $conn = Conexion::conectar();
        if ($idEstablecimiento === null) {
            $idEstablecimiento = $_SESSION['id_establecimiento'] ?? null;
        }
        if (empty($mesSeleccionado)) {
            $sql = "SELECT 
                    serie,
                    SUM(conteo) as total_conteo,
                    SUM(total) as total_importe,
                    COUNT(*) as cantidad_registros
                FROM series_ajenas 
                WHERE id_establecimiento = ? 
                AND estado = 1
                GROUP BY serie
                ORDER BY serie";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $idEstablecimiento);
        } else {
            $sql = "SELECT 
                    serie,
                    SUM(conteo) as total_conteo,
                    SUM(total) as total_importe,
                    COUNT(*) as cantidad_registros
                FROM series_ajenas 
                WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = ? 
                AND id_establecimiento = ? 
                AND estado = 1
                GROUP BY serie
                ORDER BY serie";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $mesSeleccionado, $idEstablecimiento);
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

        $sql = "SELECT * FROM series_ajenas 
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
}
