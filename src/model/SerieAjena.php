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
            id_sucursal,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['serie'],
            $data['conteo'],
            $data['total'],
            $data['user_create'],
            $data['user_update'],
            $data['id_sucursal'],
            $data['estado']
        ]);
        return true;
    }
}