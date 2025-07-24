<?php
require_once 'config/conexion.php';

class SerieSucursal
{
    public function index() {}

    public static function Insertar($data)
    {
        $conn = Conexion::conectar();
        $sql = "INSERT INTO series_sucursales (
            serie,
            codigo,
            id_establecimiento,
            user_create,
            user_update,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['serie'],
            $data['codigo'],
            $data['id_establecimiento'],
            $data['user_create'],
            $data['user_update'],
            $data['estado']
        ]);
        return true;
    }

    public static function listarSeriesPorCliente($id_establecimiento){
        $conn = Conexion::conectar();

        $sql = "
            SELECT ss.serie, ss.id_establecimiento, e.codigo_establecimiento
            FROM series_sucursales ss
            JOIN establecimiento e ON ss.id_establecimiento = e.id
            WHERE e.id_cliente = (
                SELECT id_cliente FROM establecimiento WHERE id = ?
            )
        ";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_establecimiento);
        $stmt->execute();
        $res = $stmt->get_result();
    
        $series = [];
        while ($row = $res->fetch_assoc()) {
            $series[] = $row;
        }
    
        return $series;
    }

    public static function obtenerSeriesPorEstablecimiento($id_establecimiento)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM series_sucursales WHERE id_establecimiento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_establecimiento);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }
    
    public static function obtenerTodasLasSeriesPorEstablecimiento($id_establecimiento, $soloActivas = true)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT serie FROM series_sucursales WHERE id_establecimiento = ?";
        if ($soloActivas) {
            $sql .= " AND estado = 1";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_establecimiento);
        $stmt->execute();
        $res = $stmt->get_result();
        $series = [];
        while ($row = $res->fetch_assoc()) {
            $partes = array_map('trim', explode('-', $row['serie']));
            foreach ($partes as $serie) {
                if ($serie !== '') {
                    $series[] = $serie;
                }
            }
        }
        // Eliminar duplicados y ordenar
        $series = array_unique($series);
        sort($series);
        return $series;
    }
}
