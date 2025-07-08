<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="../assets/css/output.css?v=<?php echo time(); ?>" rel="stylesheet">
  <?php if (isset($_GET['controller']) && $_GET['controller'] === 'superadmin'): ?>
    <link href="../assets/css/superadmin.css?v=<?php echo time(); ?>" rel="stylesheet">
  <?php endif; ?>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
  <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen flex flex-col bg-white text-black">
  <?php if (isset($_SESSION['id_cliente'])): ?>
    <?php require 'view/ui/navbar.php'; ?>
  <?php endif; ?>
  <main class="flex-1 w-full flex flex-col justify-start">
    <?php require $contenido; ?>
  </main>
  <?php if (isset($_SESSION['id_cliente'])): ?>
    <?php require 'view/ui/footer.php'; ?>
  <?php endif; ?>
  <script src="../assets/js/navbar.js"></script>
  <script src="https://unpkg.com/flowbite@latest/dist/flowbite.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
</body>

</html>