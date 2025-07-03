<?php
$error = $error ?? ($_GET['error'] ?? '');
?>

<div class="min-h-screen flex flex-col md:flex-row">
    <div class="md:w-1/2 flex flex-col justify-center items-center bg-gray-100 px-8 py-12 relative">
        <div class="flex flex-col items-center w-full">
            <h2 class="text-3xl md:text-4xl font-bold mb-2 text-center text-[#0018F4]">Crea tu cuenta en Escienza</h2>
            <p class="text-gray-700 text-center mb-8 text-base max-w-md">
                Regístrate para acceder a la plataforma y gestionar tus comprobantes de manera segura.
            </p>
            <div class="flex gap-6 mb-8 justify-center">
                <a href="https://facebook.com/escienza.pe" target="_blank" class="bg-white rounded-full p-2 shadow hover:bg-gray-200 transition flex items-center justify-center w-12 h-12">
                    <img src="images/facebook.svg" alt="Facebook" class="w-8 h-8">
                </a>
                <a href="https://twitter.com/escienza_pe" target="_blank" class="bg-white rounded-full p-2 shadow hover:bg-gray-200 transition flex items-center justify-center w-12 h-12">
                    <img src="images/x.svg" alt="Twitter" class="w-6 h-6">
                </a>
                <a href="https://accounts.google.com/" target="_blank" class="bg-white rounded-full p-2 shadow hover:bg-gray-200 transition flex items-center justify-center w-12 h-12">
                    <img src="images/google.svg" alt="Google" class="w-6 h-6">
                </a>
            </div>
        </div>
        <footer class="absolute bottom-4 left-0 w-full text-center text-gray-700 text-sm">
            Desarrollado por Escienza 2025.
        </footer>
    </div>
    <div class="md:w-1/2 flex flex-col justify-center items-center px-8 bg-white">
        <div class="w-full max-w-md">
            <img src="images/Logo.png" alt="Logo" class="w-25 mb-6">
            <h1 class="text-3xl font-bold mb-4 flex items-center gap-2 text-[#0018F4]">
                <span>📝</span> Crear cuenta
            </h1>
            <?php if (!empty($error)): ?>
                <div class="mb-4 text-red-600 bg-red-100 rounded px-4 py-2 animate-shake"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Paso 1: Código de acceso -->
            <div id="stepAccessToken">
                <p id="stepAccessTokenMsg" class="mb-4 text-gray-600">
                    Verifica tu código de acceso para continuar con el registro.
                </p>
                <label class="block mb-1 font-semibold text-sm text-gray-700">Código de acceso</label>
                <div class="flex gap-2">
                    <input type="text" id="inputAccessToken" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Pega tu código de acceso" required>
                    <button type="button" id="btnVerificarToken" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">Verificar</button>
                </div>
                <div id="accessTokenFeedback" class="text-sm mt-2"></div>
                <div class="mt-4 flex justify-between">
                    <a href="index.php?controller=auth&action=login" class="text-blue-600 hover:underline flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Volver al inicio de sesión
                    </a>
                </div>
            </div>

            <!-- Paso 2: Formulario de registro -->
            <form id="formRegister" method="post" action="index.php?controller=auth&action=register" class="space-y-4 mt-6 hidden" autocomplete="off">
                <p id="formRegisterMsg" class="mb-4 text-gray-600">
                    <!-- Este mensaje se mostrará solo cuando se muestre el formulario -->
                    Completa los datos para registrarte
                </p>
                <input type="hidden" name="id_establecimiento" id="reg_id_establecimiento">
                <input type="hidden" name="rol" id="reg_rol">
                <input type="hidden" name="access_token" id="reg_access_token">

                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                    <div>
                        <label class="block mb-1 font-semibold text-sm text-gray-700">Usuario</label>
                        <input type="text" name="usuario" id="reg_usuario" required placeholder="Ingrese su nombre de usuario"
                            class="w-full border rounded px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0018F4]" autocomplete="username" />
                        <div id="errorRegisterUsuario" class="text-red-600 text-sm mb-1 hidden flex items-center gap-1 pt-1"></div>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold text-sm text-gray-700">Correo electrónico</label>
                        <input type="email" name="correo" id="reg_correo" required placeholder="Ingrese su correo"
                            class="w-full border rounded px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0018F4]" autocomplete="email" />
                        <div id="errorRegisterCorreo" class="text-red-600 text-sm mb-1 hidden flex items-center gap-1 pt-1"></div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-semibold text-sm text-gray-700">Contraseña</label>
                        <div class="relative">
                            <input type="password" name="contrasena" id="reg_contrasena" required placeholder="Crea una contraseña"
                                class="w-full border rounded px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0018F4]" autocomplete="new-password" />
                            <button type="button" class="toggle-password absolute right-2 top-2 text-gray-400" data-target="reg_contrasena" tabindex="-1" aria-label="Mostrar/Ocultar contraseña">
                                <svg id="regEyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold text-sm text-gray-700">Confirmar Contraseña</label>
                        <div class="relative">
                            <input type="password" name="confirmar_contrasena" id="reg_confirmar_contrasena" required placeholder="Repite la contraseña"
                                class="w-full border rounded px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0018F4]" autocomplete="new-password" />
                            <button type="button" class="toggle-password absolute right-2 top-2 text-gray-400" data-target="reg_confirmar_contrasena" tabindex="-1" aria-label="Mostrar/Ocultar contraseña">
                                <svg id="regEyeIconConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <div id="errorRegisterContrasena" class="text-red-600 text-sm mb-1 hidden flex items-center gap-1 pt-1">
                            <!-- El icono y mensaje se insertan por JS -->
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                    <div class="flex-1">
                        <label class="block mb-1 font-semibold text-sm text-gray-700">Establecimiento</label>
                        <input type="text" id="reg_establecimiento_nombre" class="w-full border rounded px-3 py-2 bg-gray-100" disabled>
                    </div>
                    <div class="flex-1">
                        <label class="block mb-1 font-semibold text-sm text-gray-700">Rol</label>
                        <input type="text" id="reg_rol_nombre" class="w-full border rounded px-3 py-2 bg-gray-100" disabled>
                    </div>
                </div>
                <button type="submit" id="btnRegister" class="w-full bg-[#0018F4] hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow transition-colors duration-200 flex items-center justify-center gap-2">
                    <span>Registrarme</span>
                </button>
                <div class="text-center mt-4 text-sm">
                    ¿Ya tienes cuenta? <a href="index.php?controller=auth&action=login" class="text-[#0018F4] hover:underline">Inicia sesión</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Cambia el mensaje cuando se muestre el formulario de registro
    document.addEventListener('DOMContentLoaded', function() {
        const stepAccessToken = document.getElementById('stepAccessToken');
        const formRegister = document.getElementById('formRegister');
        const stepMsg = document.getElementById('stepAccessTokenMsg');
        const formMsg = document.getElementById('formRegisterMsg');
        // Este evento ya lo maneja tu JS, pero aquí aseguramos el cambio de mensaje
        document.getElementById('btnVerificarToken').addEventListener('click', function() {
            setTimeout(function() {
                if (formRegister && !formRegister.classList.contains('hidden')) {
                    stepAccessToken.classList.add('hidden');
                    formMsg.textContent = 'Completa los datos para registrarte';
                }
            }, 500);
        });
    });
</script>
<script src="../assets/js/register.js"></script>