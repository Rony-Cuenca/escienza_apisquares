<?php
    require_once 'src/config/conexion.php';
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
<body class="bg-gray-100 min-h-screen flex flex-col">

    <main>
        <!-- Header incluido aquí -->
        <?php include 'src/views/component/header.php'; ?>
        
        <!-- Contenido incluido aqui -->
        <?php include 'src/views/component/fileupload.php'; ?>

        <!-- Footer incluido aquí -->
        <?php include 'src/views/component/footer.php'; ?>
    </main>
    
</body>
</html>