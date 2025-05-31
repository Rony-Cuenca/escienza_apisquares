<?php
// Puedes mostrar mensajes de error aquí si los tienes
$error = $_GET['error'] ?? '';
?>

<div class="min-h-screen flex">
    <!-- Lado izquierdo: Ilustración y mensaje -->
    <div class="w-1/2 flex flex-col justify-center items-center bg-gray-50 px-8">
        <img src="images/Login.png" alt="Ilustración" class="max-w-xs mb-8">
        <h2 class="text-2xl font-bold mb-2 text-center">Mantén el control de tus comprobantes</h2>
        <p class="text-gray-600 text-center">
            Revisa los resúmenes de transacciones y comprobantes de cualquier tipo de negocio, todo en una plataforma segura.
        </p>
    </div>
    <!-- Lado derecho: Formulario de login -->
    <div class="w-1/2 flex flex-col justify-center items-center px-8">
        <div class="w-full max-w-md">
            <h1 class="text-3xl font-bold mb-2 flex items-center gap-2">
                <span>👋</span> Bienvenido de nuevo!
            </h1>
            <p class="mb-6 text-gray-600">Por favor inicia sesión para ingresar a tu cuenta</p>
            <?php if ($error): ?>
                <div class="mb-4 text-red-600 bg-red-100 rounded px-4 py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="index.php?controller=auth&action=login" class="space-y-4">
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Usuario</label>
                    <input type="text" name="usuario" required placeholder="Ingresa tu usuario"
                        class="w-full border rounded px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Contraseña</label>
                    <div class="relative">
                        <input type="password" name="contrasena" id="contrasena" required placeholder="Ingresa tu contraseña"
                            class="w-full border rounded px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 pr-10" />
                        <button type="button" onclick="togglePassword()" class="absolute right-2 top-2 text-gray-500">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <a href="#" class="text-blue-500 text-sm hover:underline">¿Olvidaste tu contraseña?</a>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow transition-colors duration-200">
                    Iniciar Sesión
                </button>
            </form>
            <div class="mt-6 text-center text-gray-500 text-sm">
                EDSuite es el mejor software para estaciones de servicio.
            </div>
            <div class="flex justify-center gap-2 mt-4">
                <span class="w-4 h-4 rounded-full bg-blue-900 inline-block"></span>
                <span class="w-4 h-4 rounded-full bg-blue-400 inline-block"></span>
                <span class="w-4 h-4 rounded-full bg-red-400 inline-block"></span>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('contrasena');
        const icon = document.getElementById('eyeIcon');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.add('text-blue-600');
        } else {
            input.type = "password";
            icon.classList.remove('text-blue-600');
        }
    }
</script>