<?php
require_once 'config/conexion.php';

class DiferenciaComprobante
{
    public function index()
    {
    }

    public static function Insertar($data){
        $conn = Conexion::conectar();
        $sql = "INSERT INTO diferencia_comprobante (
            serie,
            numero,
            total_sire,
            total_nubox,
            estado_sire,
            estado_nubox,
            id_establecimiento,
            fecha_registro,
            user_create,
            user_update,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['serie'],
            $data['numero'],
            $data['total_sire'],
            $data['total_nubox'],
            $data['estado_sire'],
            $data['estado_nubox'],
            $data['id_establecimiento'],
            $data['fecha_registro'],
            $data['user_create'],
            $data['user_update'],
            $data['estado']
        ]);
        return true;
    }
}