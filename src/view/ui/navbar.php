<?php
$controller = isset($_GET['controller']) ? $_GET['controller'] : '';
?>

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