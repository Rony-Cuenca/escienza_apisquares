<?php
$controller = isset($_GET['controller']) ? $_GET['controller'] : '';
$nombreUsuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '';
$rolUsuario = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$correoUsuario = isset($_SESSION['correo']) ? $_SESSION['correo'] : '';
?>
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/50 shadow-sm">
  <div class="max-w-screen-xl mx-auto flex items-center justify-between px-4 md:px-8 py-4">
    <!-- Logo -->
    <div class="flex items-center">
      <a href="index.php?controller=home&action=index" class="flex items-center gap-3">
        <img class="w-auto h-10" src="images/Logo.png" alt="Logo ESCIENZA" />
      </a>
    </div>

    <!-- Menú principal (escritorio) -->
    <div class="hidden md:flex items-center">
      <ul class="flex gap-2 font-medium">
        <?php if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']): ?>
          <li>
            <a href="index.php?controller=superadmin"
              class="<?= $controller === 'superadmin'
                        ? 'inline-flex items-center gap-2 px-3 py-2 bg-blue-500 text-white font-bold rounded-lg border-2 border-blue-600'
                        : 'inline-flex items-center gap-2 px-3 py-2 bg-slate-100 text-slate-700 font-medium rounded-lg border-2 border-slate-300 hover:bg-blue-100 hover:text-blue-600 hover:border-blue-400 transition-all duration-200' ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="<?= $controller === 'superadmin' ? 'w-4 h-4 text-white' : 'w-4 h-4 text-slate-600' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              Dashboard
            </a>
          </li>
          <li>
            <a href="index.php?controller=superadmin&action=clientes"
              class="inline-flex items-center gap-2 px-3 py-2 bg-slate-100 text-slate-700 font-medium rounded-lg border-2 border-slate-300 hover:bg-blue-100 hover:text-blue-600 hover:border-blue-400 transition-all duration-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Clientes
            </a>
          </li>
        <?php else: ?>
          <!-- Menú Normal -->
          <li>
            <a href="index.php?controller=home&action=index"
              class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'home' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
              Home
            </a>
          </li>
          <li>
            <a href="index.php?controller=cuadres&action=index"
              class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'cuadres' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
              Cuadre
            </a>
          </li>
          <li class="relative">
            <button type="button"
              class="flex items-center gap-1 py-2 px-3 rounded-sm transition-colors hover:text-blue-700 focus:outline-none"
              id="btnUsuariosEstablecimientos">
              Usuarios & Establecimientos
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg border hidden z-50"
              id="submenuUsuariosEstablecimientos">
              <a href="index.php?controller=usuario&action=index"
                class="block px-4 py-2 hover:bg-blue-50 text-gray-900">Usuarios</a>
              <a href="index.php?controller=establecimiento"
                class="block px-4 py-2 hover:bg-blue-50 text-gray-900">Establecimientos</a>
            </div>
          </li>
          <li>
            <a href="index.php?controller=reporte&action=index"
              class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'reporte' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
              Reportes
            </a>
          </li>
        <?php endif; ?>
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
          <?php if (isset($_SESSION['impersonating']) && $_SESSION['impersonating']): ?>
            <li>
              <a href="index.php?controller=superadmin&action=volverSuperAdmin" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50 font-semibold">
                🔙 Volver a Super Admin
              </a>
            </li>
            <li>
              <hr class="my-1">
            </li>
            <li>
              <hr class="my-1">
            </li>
          <?php endif; ?>
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
      <?php if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']): ?>
        <!-- Menú Super Admin móvil -->
        <li>
          <a href="index.php?controller=superadmin"
            class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'superadmin' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
            Dashboard
          </a>
        </li>
        <li>
          <a href="index.php?controller=superadmin&action=clientes"
            class="block py-2 px-3 rounded-sm transition-colors hover:text-blue-700">
            Clientes
          </a>
        </li>
      <?php else: ?>
        <!-- Menú normal móvil -->
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
      <?php endif; ?>
      <li>
        <a href="index.php?controller=auth&action=logout"
          class="block py-2 px-3 rounded-sm transition-colors">
          Cerrar Sesión
        </a>
      </li>
    </ul>
  </div>
</nav>