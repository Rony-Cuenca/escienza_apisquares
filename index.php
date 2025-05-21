<?php
    require_once 'src/config/conexion.php';
    include 'src/views/component/cabecera';
    //header('Location: http://localhost/ESCIENZA_APISQUARE/config/consulta.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApiQuare</title>
    <link href="ESCIENZA_APISQUARE/src/output.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Subir 3 archivos de Excel</h2>
        <form action="public/upload_excel.php" method="post" enctype="multipart/form-data">
            <label for="excel1">Archivo Excel 1:</label>
            <input type="file" name="excel[]" id="excel1" accept=".xls,.xlsx" required>

            <label for="excel2">Archivo Excel 2:</label>
            <input type="file" name="excel[]" id="excel2" accept=".xls,.xlsx" required>

            <label for="excel3">Archivo Excel 3:</label>
            <input type="file" name="excel[]" id="excel3" accept=".xls,.xlsx" required>

            <button type="submit">Subir Archivos</button>
        </form>
    </div>
    <?php include 'src/views/component/footer'; ?>
</body>
</html>