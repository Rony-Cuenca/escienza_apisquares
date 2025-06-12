<?php
$controller = isset($_GET['controller']) ? $_GET['controller'] : '';
?>
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