<?php
$controller = isset($_GET['controller']) ? $_GET['controller'] : '';
$nombreUsuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '';
$rolUsuario = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$correoUsuario = isset($_SESSION['correo']) ? $_SESSION['correo'] : '';
?>
<nav class="sticky top-0 z-50 bg-slate-100 border-b border-gray-200">
  <div class="max-w-screen-xl mx-auto flex items-center justify-between px-2 md:px-8 py-4">
    <!-- Logo -->
    <div class="flex items-center">
      <a href="index.php?controller=home&action=index" class="flex items-center gap-3">
        <img class="w-auto h-11" src="images/Logo.png" alt="Logo ESCIENZA" />
      </a>
    </div>
    <!-- Menú principal (escritorio) -->
    <div class="hidden md:flex items-center">
      <ul class="flex gap-8 font-medium">
        <li>
          <a href="index.php?controller=home&action=index"
            class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'home' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
            Home
          </a>
        </li>
        <li>
          <a href="index.php?controller=cuadres&action=index"
            class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'cuadres' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
            Cuadre de Ventas
          </a>
        </li>
        <li>
          <a href="index.php?controller=usuario&action=index"
            class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'usuario' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
            Usuario
          </a>
        </li>
        <li>
          <a href="index.php?controller=establecimiento"
            class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'establecimiento' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
            Establecimiento
          </a>
        </li>
      </ul>
    </div>
    <!-- Menú de usuario -->
    <div class="hidden md:flex items-center">
      <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
        <span class="sr-only">Abrir menú de usuario</span>
        <img class="w-8 h-8 rounded-full" src="images/ic_user.png" alt="user photo">
      </button>
      <!-- Menú Desplegable -->
      <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm" id="user-dropdown">
        <div class="px-4 py-3">
          <span class="block text-sm text-gray-900">
            <?= htmlspecialchars($rolUsuario) ?><?= $rolUsuario && $nombreUsuario ? ' - ' : '' ?><?= htmlspecialchars($nombreUsuario) ?>
          </span>
          <span class="block text-sm text-gray-500 truncate">
            <?= htmlspecialchars($correoUsuario) ?>
          </span>
        </div>
        <ul class="py-2" aria-labelledby="user-menu-button">
          <li>
            <a href="index.php?controller=home&action=index" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
          </li>
          <li>
            <a href="index.php?controller=configuracion&action=index" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Configuración</a>
          </li>
          <li>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ganancias</a>
          </li>
          <li>
            <a href="index.php?controller=auth&action=logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cerrar Sesión</a>
          </li>
        </ul>
      </div>
    </div>
    <!-- Botón hamburguesa -->
    <button data-collapse-toggle="navbar-user" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm rounded-lg md:hidden focus:outline-none focus:ring-2" aria-controls="navbar-user" aria-expanded="false">
      <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
      </svg>
    </button>
  </div>
  <!-- Menú desplegable móvil -->
  <div class="hidden md:hidden px-4 pb-4" id="navbar-user">
    <div class="py-3 border-b">
      <span class="block text-sm text-gray-900">
        <?= htmlspecialchars($rolUsuario) ?><?= $rolUsuario && $nombreUsuario ? ' - ' : '' ?><?= htmlspecialchars($nombreUsuario) ?>
      </span>
      <span class="block text-sm text-gray-500 truncate">
        <?= htmlspecialchars($correoUsuario) ?>
      </span>
    </div>
    <ul class="flex flex-col gap-2 font-medium mt-4">
      <li>
        <a href="index.php?controller=home&action=index"
          class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'home' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
          Dashboard
        </a>
      </li>
      <li>
        <a href="index.php?controller=cuadres&action=index"
          class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'cuadres' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
          Cuadre de Ventas
        </a>
      </li>
      <li>
        <a href="index.php?controller=usuario&action=index"
          class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'usuario' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
          Usuario
        </a>
      </li>
      <li>
        <a href="index.php?controller=establecimiento"
          class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'establecimiento' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
          Establecimiento
        </a>
      </li>
      <li>
        <a href="index.php?controller=configuracion&action=index"
          class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'configuracion' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
          Configuración
        </a>
      </li>
      <li>
        <a href="index.php?controller=auth&action=logout"
          class="block py-2 px-3 rounded-sm transition-colors">
          Cerrar Sesión
        </a>
      </li>
    </ul>
  </div>
</nav>