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
            id_sucursal,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['producto'],
            $data['cantidad'],
            $data['total'],
            $data['user_create'],
            $data['user_update'],
            $data['id_sucursal'],
            $data['estado']
        ]);
        return true;
    }
}