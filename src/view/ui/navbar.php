<?php
$controller = isset($_GET['controller']) ? $_GET['controller'] : '';
?>
<<<<<<< HEAD
<header>
    <nav id="navbar" class="fixed top-0 left-0 w-full z-50 bg-white px-[80px] py-4 flex justify-between items-center shadow transition-colors duration-300">
        <div>
            <img class="w-17.5 h-10" src="images/Logo.png" alt="Logo ESCIENZA" />
        </div>
        <div>
            <ul class="flex items-center gap-[5vw] font-light text-[18px]">
                <li>
                    <a href="index.php?controller=home&action=index"
                        class="<?= $controller === 'home'
                                    ? 'text-[#0018F4] font-semibold underline'
                                    : 'text-gray-800' ?> hover:text-[#2746c7] hover:font-semibold hover:underline tracking-[0.15px] transition-colors duration-200">
                        Inicio
                    </a>
                </li>
                <li>
                    <a href="index.php?controller=cuadres&action=index"
                        class="<?= $controller === 'cuadres'
                                    ? 'text-[#0018F4] font-semibold underline'
                                    : 'text-gray-800' ?> hover:text-[#2746c7] hover:font-semibold hover:underline tracking-[0.15px] transition-colors duration-200">
                        Cuadre Ventas
                    </a>
                </li>
                <li>
                    <a href="index.php?controller=usuario&action=index"
                        class="<?= $controller === 'usuario'
                                    ? 'text-[#0018F4] font-semibold underline'
                                    : 'text-gray-800' ?> hover:text-[#2746c7] hover:font-semibold hover:underline tracking-[0.15px] transition-colors duration-200">
                        Usuario
                    </a>
                </li>
                <li>
                    <a href="index.php?controller=cliente&action=index"
                        class="<?= $controller === 'cliente'
                                    ? 'text-[#0018F4] font-semibold underline'
                                    : 'text-gray-800' ?> hover:text-[#2746c7] hover:font-semibold hover:underline tracking-[0.15px] transition-colors duration-200">
                        Cliente
                    </a>
                </li>
            </ul>
        </div>
        <div class="flex items-center gap-4">
            <div class="relative">
                <button id="btnAdminMenu" type="button"
                    class="bg-[#0018F4] text-white text-[14px] font-medium hover:bg-[#2746c7] rounded-[15px] focus:outline-none flex items-center gap-2 w-[125px] h-[45px] px-4 py-2 transition-colors duration-200 shadow-md">
                    <img src="images/ic_user.png" class="w-[20px] h-[20px] inline-block" />
                    Admin
                    <img src="images/ic_arrow_down.png" class="w-[10px] h-[50px] inline-block ml-1 object-contain" />
                </button>
                <div id="adminDropdown"
                    class="absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg opacity-0 invisible transition-all duration-300 z-10">
                    <a href="index.php?controller=configuracion&action=index"
                        class="block px-4 py-2 text-gray-800 hover:bg-[#f0f4ff] transition-colors duration-200">Configuración</a>
                    <a href="index.php?controller=auth&action=logout"
                        class="block px-4 py-2 text-gray-800 hover:bg-[#ffeaea] transition-colors duration-200">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </nav>
    <style>
        #navbar {
            box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
        }

        #adminDropdown.show {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0);
        }

        #adminDropdown {
            transform: translateY(-10px);
        }
    </style>
</header>
=======

<nav class="bg-slate-100 border-gray-200">
  <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
  <a href="https://flowbite.com/" class="flex items-center space-x-3 rtl:space-x-reverse">
      <img class="w-auto h-11" src="images/Logo.png" alt="Logo ESCIENZA" />
  </a>
  <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
      <button type="button" class="flex text-sm bg-gray-800 rounded-full md:me-0 focus:ring-4 focus:ring-gray-300" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
        <span class="sr-only">Open user menu</span>
        <img class="w-8 h-8 rounded-full" src="images/ic_user.png" alt="user photo">
      </button>
      <!-- Dropdown menu -->
      <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm" id="user-dropdown">
        <div class="px-4 py-3">
          <span class="block text-sm text-gray-900">Admin</span>
          <span class="block text-sm  text-gray-500 truncate">Admin@flowbite.com</span>
        </div>
        <ul class="py-2" aria-labelledby="user-menu-button">
          <li>
            <a href="index.php?controller=home&action=index" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
          </li>
          <li>
            <a href="index.php?controller=configuracion&action=index" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
          </li>
          <li>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Earnings</a>
          </li>
          <li>
            <a href="index.php?controller=auth&action=logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
          </li>
        </ul>
      </div>
      <button data-collapse-toggle="navbar-user" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm rounded-lg md:hidden focus:outline-none focus:ring-2" aria-controls="navbar-user" aria-expanded="false">
        <span class="sr-only">Open main menu</span>
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
        </svg>
    </button>
  </div>
  <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-user">
    <ul class="flex flex-col font-medium p-4 md:p-0 mt-4 border md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0">
      <li>
        <a href="index.php?controller=home&action=index" class="block py-2 px-3 rounded-sm md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ">Dashboard</a>
      </li>
      <li>
        <a href="index.php?controller=cuadres&action=index" class="block py-2 px-3 rounded-sm md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ">Cuadre de Ventas</a>
      </li>
      <li>
        <a href="index.php?controller=usuario&action=index" class="block py-2 px-3 rounded-sm md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ">Cliente</a>
      </li>
      <li>
        <a href="index.php?controller=cliente&action=index" class="block py-2 px-3 rounded-sm md:hover:bg-transparent md:hover:text-blue-700 md:p-0 ">Establecimiento</a>
      </li>
    </ul>
  </div>
  </div>
</nav>
>>>>>>> 3fdc209 (DETAILS IN THE DESIGN OF THE NAVBAR AND FOOT)
