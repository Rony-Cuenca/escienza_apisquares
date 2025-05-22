<?php
require_once 'src/config/Conexion.php';

class Cliente {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    public function get_clientes() {
        $stmt = $this->pdo->prepare("SELECT * FROM cliente WHERE estado = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_totalsales(){
        // Consulta SQL: Ventas totales por sucursal
    $sql = "SELECT
        s.razon_social AS Sucursal,
        SUM(r.monto_total) AS MontoTotal
    FROM rescomprobante r
    JOIN sucursal s ON r.id_sucursal = s.id
    GROUP BY s.razon_social
    ORDER BY s.razon_social;";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //ESTO EN VIEW
    if ($resultados) {
        echo "<table border='1'>";
        echo "<tr><th>Sucursal</th><th>Monto Total</th></tr>";
        foreach ($resultados as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Sucursal']) . "</td>";
            echo "<td>" . htmlspecialchars($row['MontoTotal']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No se encontraron resultados.";
    }
    //-------------------------
    }

}