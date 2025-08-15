<?php
require_once 'config/conexion.php';

class DiferenciaComprobante
{
    public function index() {}

    public static function Insertar($data)
    {
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

    public static function obtenerIncidencias($mes, $idEstablecimiento = null)
    {
        $conn = Conexion::conectar();
        $id_cliente = $_SESSION['id_cliente'] ?? null;

        $sql = "SELECT 
            e.etiqueta AS establecimiento,
            dc.serie,
            dc.numero,
            dc.total_sire,
            dc.total_nubox,
            dc.estado_sire,
            dc.estado_nubox
        FROM diferencia_comprobante dc
        INNER JOIN establecimiento e ON e.id = dc.id_establecimiento
        WHERE LEFT(dc.fecha_registro, 7) = ? AND e.id_cliente = ?";

        $params = [$mes, $id_cliente];

        if ($idEstablecimiento !== null) {
            $sql .= " AND dc.id_establecimiento = ?";
            $params[] = $idEstablecimiento;
        }

        $sql .= " ORDER BY e.etiqueta, dc.serie, dc.numero";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->get_result();
        $incidencias = [];
        while ($row = $result->fetch_assoc()) {
            $incidencias[] = $row;
        }
        return $incidencias;
    }
}
