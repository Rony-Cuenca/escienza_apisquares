<?php
require_once 'config/conexion.php';

class SerieSucursal
{
    public function index()
    {
        
    }

    public static function Insertar($data){
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

    public static function obtenerSeriesPorEstablecimiento($id_establecimiento) {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM series_sucursales WHERE id_establecimiento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_establecimiento);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }
}