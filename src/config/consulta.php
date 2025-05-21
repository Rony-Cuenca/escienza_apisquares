<?php
    require_once 'conexion.php';

    // Obtener la conexión PDO
    $pdo = Conexion::getConexion();

    // Consulta SQL: Ventas totales por sucursal
    $sql = "SELECT
        s.razon_social AS Sucursal,
        SUM(r.monto_total) AS MontoTotal
    FROM rescomprobante r
    JOIN sucursal s ON r.id_sucursal = s.id
    GROUP BY s.razon_social
    ORDER BY s.razon_social;";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

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