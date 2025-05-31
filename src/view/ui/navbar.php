<?php
$controller = isset($_GET['controller']) ? $_GET['controller'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <header>
        <nav class="bg-white px-[80px] py-4 flex justify-between items-center shadow-[0_4px_4px_0_rgba(0,0,0,0.25)]">
            <div>
                <img class="w-17.5 h-10" src="images/Logo.png" alt="Logo ESCIENZA" />
            </div>
            <div>
                <ul class="flex items-center gap-[5vw] font-light  text-[18px]">
                    <li>
                        <a href="index.php?controller=home&action=index"
                            class="hover:text-[#0018F4] hover:font-semibold hover:underline tracking-[0.15px]<?php if ($controller === 'home') echo ' font-semibold underline text-[#0018F4]'; ?>">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=cuadres&action=index"
                            class="hover:text-[#0018F4] hover:font-semibold hover:underline tracking-[0.15px]<?php if ($controller === 'cuadres') echo ' font-semibold underline text-[#0018F4]'; ?>">
                            Cuadre Ventas
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=usuario&action=index"
                            class="hover:text-[#0018F4] hover:font-semibold hover:underline tracking-[0.15px]<?php if ($controller === 'usuario') echo ' font-semibold underline text-[#0018F4]'; ?>">
                            Usuarios
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=cliente&action=index"
                            class="hover:text-[#0018F4] hover:font-semibold hover:underline tracking-[0.15px]<?php if ($controller === 'cliente') echo ' font-semibold underline text-[#0018F4]'; ?>">
                            Cliente
                        </a>
                    </li>
                </ul>
            </div>
            <div class="relative group">
                <button class="bg-[#0018F4] text-white text-[14px] font-medium hover:bg-[#212d9b] rounded-[15px] focus:outline-none flex items-center gap-2 w-[125px] h-[45px] px-4 py-2">
                    <img src="images/ic_user.png" class="w-[20px] h-[20px] inline-block" />
                    Admin
                    <img src="images/ic_arrow_down.png" class="w-[10px] h-[50px]inline-block ml-1 object-contain" />
                </button>
                <div class="absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity duration-150 z-10">
                    <a href="index.php?controller=configuracion&action=index" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Configuración</a>
                    <a href="index.php?controller=auth&action=logout" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Cerrar sesión</a>
                </div>
            </div>
        </nav>
    </header>
</body>

</html>