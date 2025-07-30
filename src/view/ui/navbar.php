<?php
require_once __DIR__ . '/../../helpers/permisos_helper.php';
if (!function_exists('obtenerContextoActual')) {
  function obtenerContextoActual()
  {
    $es_modo_directo = isset($_SESSION['superadmin_mode']) && $_SESSION['superadmin_mode'] === true;
    $es_superadmin = esSuperAdmin();

    return [
      'es_modo_directo' => $es_modo_directo,
      'es_superadmin' => $es_superadmin,
      'establecimiento_id' => $_SESSION['establecimiento_id'] ?? $_SESSION['acting_as_establecimiento'] ?? null,
      'usuario_id' => $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null,
      'rol' => $_SESSION['rol'] ?? ''
    ];
  }
}

$controller = isset($_GET['controller']) ? $_GET['controller'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$nombreUsuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '';
$rolUsuario = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$correoUsuario = isset($_SESSION['correo']) ? $_SESSION['correo'] : '';
$contexto = obtenerContextoActual();
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
        <?php if ((isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) && !$contexto['es_modo_directo']): ?>
          <!-- Menú SuperAdmin -->
          <li>
            <a href="index.php?controller=superadmin"
              class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'superadmin' && $action !== 'clientes' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
              Panel de Control
            </a>
          </li>
          <li>
            <a href="index.php?controller=superadmin&action=clientes"
              class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'superadmin' && $action === 'clientes' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
              Clientes
            </a>
          </li>
        <?php else: ?>
          <!-- Menú Normal del Establecimiento -->
          <li>
            <a href="index.php?controller=home&action=<?= $contexto['es_modo_directo'] ? 'dashboard' : 'index' ?>"
              class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'home' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
              Home
            </a>
          </li>
          <?php if (puedeHacerCuadres()): ?>
            <li>
              <a href="index.php?controller=cuadres&action=index"
                class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'cuadres' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
                Cuadre
              </a>
            </li>
          <?php endif; ?>
          <?php if (puedeGestionarUsuarios() || puedeGestionarEstablecimientos()): ?>
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
                <?php if (puedeGestionarUsuarios()): ?>
                  <a href="index.php?controller=usuario&action=index"
                    class="block px-4 py-2 hover:bg-blue-50 text-gray-900">Usuarios</a>
                <?php endif; ?>
                <?php if (puedeGestionarEstablecimientos()): ?>
                  <a href="index.php?controller=establecimiento"
                    class="block px-4 py-2 hover:bg-blue-50 text-gray-900">Establecimientos</a>
                <?php endif; ?>
              </div>
            </li>
          <?php endif; ?>
          <?php if (puedeVerReportes()): ?>
            <li>
              <a href="index.php?controller=reporte&action=index"
                class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'reporte' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
                Reportes
              </a>
            </li>
          <?php endif; ?>
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
            <?php if ($contexto['es_modo_directo']): ?>
              Super Admin - <?= htmlspecialchars($nombreUsuario) ?>
            <?php else: ?>
              <?= htmlspecialchars($rolUsuario) ?><?= $rolUsuario && $nombreUsuario ? ' - ' : '' ?><?= htmlspecialchars($nombreUsuario) ?>
            <?php endif; ?>
          </span>
          <span class="block text-sm text-gray-500 truncate">
            <?= htmlspecialchars($correoUsuario) ?>
          </span>
        </div>
        <ul class="py-2" aria-labelledby="user-menu-button">
          <?php if ($contexto['es_modo_directo']): ?>
            <li>
              <a href="index.php?controller=superadmin&action=salirAccesoDirecto"
                class="block px-4 py-2 text-sm text-purple-700 hover:bg-purple-50 font-semibold"
                onclick="return confirm('¿Salir del modo SuperAdmin?')">
                🔙 Volver a SuperAdmin
              </a>
            </li>
            <li>
              <hr class="my-1">
            </li>
          <?php endif; ?>
          <li>
            <a href="index.php?controller=home&action=<?= $contexto['es_modo_directo'] ? 'dashboard' : 'index' ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
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
        <?php if ($contexto['es_modo_directo']): ?>
          Super Admin - <?= htmlspecialchars($nombreUsuario) ?>
        <?php else: ?>
          <?= htmlspecialchars($rolUsuario) ?><?= $rolUsuario && $nombreUsuario ? ' - ' : '' ?><?= htmlspecialchars($nombreUsuario) ?>
        <?php endif; ?>
      </span>
      <span class="block text-sm text-gray-500 truncate">
        <?= htmlspecialchars($correoUsuario) ?>
      </span>
    </div>
    <ul class="flex flex-col gap-2 font-medium mt-4">
      <?php if ($contexto['es_modo_directo']): ?>
        <li>
          <a href="index.php?controller=superadmin&action=salirAccesoDirecto"
            class="block py-2 px-3 rounded-sm bg-purple-100 text-purple-700 font-semibold hover:bg-purple-200 transition-colors"
            onclick="return confirm('¿Salir del modo SuperAdmin?')">
            🔙 Volver a SuperAdmin
          </a>
        </li>
        <li>
          <hr class="border-gray-200">
        </li>
      <?php endif; ?>

      <?php if ((isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) && !$contexto['es_modo_directo']): ?>
        <!-- Menú Super Admin móvil -->
        <li>
          <a href="index.php?controller=superadmin"
            class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'superadmin' && $action !== 'clientes' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
            Panel de Control
          </a>
        </li>
        <li>
          <a href="index.php?controller=superadmin&action=clientes"
            class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'superadmin' && $action === 'clientes' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
            Clientes
          </a>
        </li>
      <?php else: ?>
        <!-- Menú normal móvil -->
        <li>
          <a href="index.php?controller=home&action=<?= $contexto['es_modo_directo'] ? 'dashboard' : 'index' ?>"
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
          <a href="index.php?controller=reporte&action=index"
            class="block py-2 px-3 rounded-sm transition-colors <?= $controller === 'reporte' ? 'text-blue-700 font-bold' : 'hover:text-blue-700' ?>">
            Reportes
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