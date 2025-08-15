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
        $id_cliente = $_SESSION['id_cliente'] ?? null;

        if (empty($mesSeleccionado)) {
            $sql = "SELECT 
                sa.serie,
                SUM(sa.conteo) as total_conteo,
                SUM(sa.total) as total_importe,
                COUNT(*) as cantidad_registros
            FROM series_ajenas sa
            INNER JOIN establecimiento e ON sa.id_establecimiento = e.id
            WHERE sa.estado = 1 AND e.id_cliente = ?";

            $params = [$id_cliente];

            if ($idEstablecimiento !== null) {
                $sql .= " AND sa.id_establecimiento = ?";
                $params[] = $idEstablecimiento;
            }

            $sql .= " GROUP BY sa.serie ORDER BY sa.serie";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {
            $sql = "SELECT 
                sa.serie,
                SUM(sa.conteo) as total_conteo,
                SUM(sa.total) as total_importe,
                COUNT(*) as cantidad_registros
            FROM series_ajenas sa
            INNER JOIN establecimiento e ON sa.id_establecimiento = e.id
            WHERE DATE_FORMAT(sa.fecha_registro, '%Y-%m') = ? 
            AND sa.estado = 1 
            AND e.id_cliente = ?";

            $params = [$mesSeleccionado, $id_cliente];

            if ($idEstablecimiento !== null) {
                $sql .= " AND sa.id_establecimiento = ?";
                $params[] = $idEstablecimiento;
            }

            $sql .= " GROUP BY sa.serie ORDER BY sa.serie";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }

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
