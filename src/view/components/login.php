<?php
$error = $error ?? ($_GET['error'] ?? '');
?>

<div class="min-h-screen flex flex-col md:flex-row">
    <div class="md:w-1/2 flex flex-col justify-center items-center bg-gray-100 px-8 py-12 relative">
        <div class="flex flex-col items-center w-full">
            <h2 class="text-3xl md:text-4xl font-bold mb-2 text-center text-[#0018F4]">Mantén el control de tus comprobantes</h2>
            <p class="text-gray-700 text-center mb-8 text-base max-w-md">
                Revisa los resúmenes de transacciones y comprobantes de cualquier tipo de negocio, todo en una plataforma segura.
            </p>
            <div class="flex space-x-4 mb-8">
                <a href="https://facebook.com/escienza.pe" target="_blank" class="bg-white rounded-full p-2 shadow hover:bg-gray-200 transition">
                    <img src="images/facebook.svg" alt="Facebook" class="w-6 h-6">
                </a>
                <a href="https://twitter.com/escienza_pe" target="_blank" class="bg-white rounded-full p-2 shadow hover:bg-gray-200 transition">
                    <img src="images/x.svg" alt="Twitter" class="w-6 h-6">
                </a>
                <a href="https://accounts.google.com/" target="_blank" class="bg-white rounded-full p-2 shadow hover:bg-gray-200 transition">
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
            <h1 class="text-3xl font-bold mb-2 flex items-center gap-2 text-[#0018F4]">
                <span>👋</span> Bienvenido de nuevo!
            </h1>
            <p class="mb-6 text-gray-600">Por favor inicia sesión para ingresar a tu cuenta</p>
            <?php if (!empty($error)): ?>
                <div class="mb-4 text-red-600 bg-red-100 rounded px-4 py-2 animate-shake"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="index.php?controller=auth&action=login" class="space-y-4" autocomplete="off">
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Usuario</label>
                    <input type="text" name="usuario" required placeholder="Ingresa tu usuario"
                        class="w-full border rounded px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0018F4]" />
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Contraseña</label>
                    <div class="relative">
                        <input type="password" name="contrasena" id="contrasena" required placeholder="Ingresa tu contraseña"
                            class="w-full border rounded px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0018F4]" />
                        <button type="button" onclick="togglePassword()" class="absolute right-2 top-2 text-gray-400" tabindex="-1">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-[#0018F4] hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow transition-colors duration-200">
                    Iniciar Sesión
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    @keyframes shake {

        10%,
        90% {
            transform: translateX(-2px);
        }

        20%,
        80% {
            transform: translateX(4px);
        }

        30%,
        50%,
        70% {
            transform: translateX(-8px);
        }

        40%,
        60% {
            transform: translateX(8px);
        }
    }

    .animate-shake {
        animation: shake 0.4s;
    }
</style>

<script>
    function togglePassword() {
        const input = document.getElementById('contrasena');
        const icon = document.getElementById('eyeIcon');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.add('text-[#0018F4]');
        } else {
            input.type = "password";
            icon.classList.remove('text-[#0018F4]');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action*="login"]');
        const usuario = form.querySelector('input[name="usuario"]');
        const contrasena = form.querySelector('input[name="contrasena"]');
        const btn = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function(e) {
            let errorMsg = '';
            if (!usuario.value.trim()) {
                errorMsg = 'El usuario es obligatorio.';
            } else if (!contrasena.value.trim()) {
                errorMsg = 'La contraseña es obligatoria.';
            }
            if (errorMsg) {
                e.preventDefault();
                alert(errorMsg);
                return false;
            }
            btn.disabled = true;
            btn.textContent = 'Procesando...';
        });
    });
</script>