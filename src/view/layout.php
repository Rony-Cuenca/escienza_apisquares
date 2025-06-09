<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/escienza_apisquares/assets/css/output.css" rel="stylesheet">
  <title>Escienza - Cuadres</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex flex-col">
  <?php if (isset($_SESSION['id_cliente'])): ?>
    <?php require 'view/ui/navbar.php'; ?>
  <?php endif; ?>
  <main class="flex-1 w-full">
    <div class="w-full">
      <?php require $contenido; ?>
    </div>
  </main>
  <?php if (isset($_SESSION['id_cliente'])): ?>
    <?php require 'view/ui/footer.php'; ?>
  <?php endif; ?>
</body>

</html>