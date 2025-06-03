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
                id_reporte,
                user_create,
                user_update,
                id_sucursal,
                estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $data['serie'],
                $data['cantidad_compr'],
                $data['suma_gravada'],
                $data['suma_exonerada'],
                $data['suma_inafecto'],
                $data['suma_igv'],
                $data['monto_total'],
                $data['id_reporte'],
                $data['user_create'],
                $data['user_update'],
                $data['id_sucursal'],
                $data['estado']
            ]);
            return true;
    }

}