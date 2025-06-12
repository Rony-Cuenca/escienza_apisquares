<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/assets/css/output.css" rel="stylesheet">
</head>

<body class="min-h-screen flex flex-col bg-white text-black">
  <?php if (isset($_SESSION['id_cliente'])): ?>
    <?php require 'view/ui/navbar.php'; ?>
    <div style="height:90px"></div>
  <?php endif; ?>
  <main class="flex-1 w-full">
    <div>
      <?php require $contenido; ?>
    </div>
  </main>
  <?php if (isset($_SESSION['id_cliente'])): ?>
    <?php require 'view/ui/footer.php'; ?>
  <?php endif; ?>
</body>

<script src="../assets/js/navbar.js"></script>
<script src="https://unpkg.com/flowbite@latest/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</html>