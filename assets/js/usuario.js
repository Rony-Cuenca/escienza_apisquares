document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalUsuario');
    const modalConfirmacion = document.getElementById('modalConfirmacion');
    const modalAnimacion = document.getElementById('modalAnimacion');
    const modalBotones = document.getElementById('modalBotones');
    const modalTitulo = document.getElementById('modalTitulo');
    const modalMensaje = document.getElementById('modalMensaje');
    const btnNuevoUsuario = document.getElementById('btnNuevoUsuario');
    const btnCerrarModal = document.getElementById('btnCerrarModal');
    const btnCancelarModal = document.getElementById('btnCancelarModal');
    const btnAceptarConfirmacion = document.getElementById('btnAceptarConfirmacion');
    const btnCancelarConfirmacion = document.getElementById('btnCancelarConfirmacion');
    const formUsuario = document.getElementById('formUsuario');
    const correoCliente = btnNuevoUsuario.dataset.correoCliente;

    const modalInputs = {
        id: document.getElementById('modalIdUsuario'),
        nombre: document.getElementById('modalUsuarioNombre'),
        correo: document.getElementById('modalUsuarioCorreo'),
        rol: document.getElementById('modalUsuarioRol'),
        sucursal: document.getElementById('modalUsuarioSucursal'),
        contraseña: document.getElementById('modalUsuarioContraseña'),
        confirmarContraseña: document.getElementById('modalUsuarioConfirmarContraseña'),
        estado: document.getElementById('modalUsuarioEstado')
    };

    let idUsuario = null;
    let estadoUsuario = null;

    function abrirModalUsuario({titulo, action, values = {}}) {
        cerrarMenus();
        modalTitulo.textContent = titulo;
        formUsuario.action = action;
        modalInputs.id.value = values.id || '';
        modalInputs.nombre.value = values.nombre || '';
        modalInputs.correo.value = values.correo || correoCliente || 'Correo no disponible';
        modalInputs.correo.readOnly = true;
        modalInputs.rol.value = values.rol || '';
        modalInputs.sucursal.value = values.sucursal || '';
        modalInputs.estado.value = values.estado || '1';
        modalInputs.contraseña.value = '';
        modalInputs.confirmarContraseña.value = '';
        modal.classList.remove('hidden');
    }

    function cerrarModalUsuario() {
        modal.classList.add('hidden');
    }

    function resetModalConfirmacion() {
        modalAnimacion.classList.add('hidden');
        modalAnimacion.innerHTML = '';
        modalBotones.classList.remove('hidden');
        modalMensaje.textContent = '¿Estás seguro de cambiar el estado de este usuario?';
        idUsuario = null;
        estadoUsuario = null;
    }

    function cerrarMenus() {
        document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
            const menu = btn.nextElementSibling;
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    }

    btnNuevoUsuario.addEventListener('click', () => {
        abrirModalUsuario({titulo: 'Nuevo Usuario', action: 'index.php?controller=usuario&action=crear'});
    });

    document.querySelectorAll('[data-action="editar"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            abrirModalUsuario({
                titulo: 'Editar Usuario',
                action: 'index.php?controller=usuario&action=editar',
                values: {
                    id: btn.dataset.id,
                    nombre: btn.dataset.usuario,
                    correo: btn.dataset.correo,
                    rol: btn.dataset.rol,
                    sucursal: btn.dataset.sucursal,
                    estado: btn.dataset.estado
                }
            });
        });
    });

    btnCerrarModal.addEventListener('click', cerrarModalUsuario);
    btnCancelarModal.addEventListener('click', cerrarModalUsuario);

    document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            cerrarMenus();
            const menu = btn.nextElementSibling;
            if (menu) menu.classList.toggle('hidden');
        });
    });
    document.addEventListener('click', cerrarMenus);

    document.querySelectorAll('[data-action="cambiarEstado"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            idUsuario = btn.dataset.id;
            estadoUsuario = btn.dataset.estado;
            if (!idUsuario || !estadoUsuario) {
                alert('Datos inválidos para cambiar el estado.');
                return;
            }
            modalMensaje.textContent = '¿Estás seguro de cambiar el estado de este usuario?';
            modalConfirmacion.classList.remove('hidden');
        });
    });

    btnCancelarConfirmacion.addEventListener('click', () => {
        modalConfirmacion.classList.add('hidden');
        resetModalConfirmacion();
    });

    btnAceptarConfirmacion.addEventListener('click', () => {
        modalBotones.classList.add('hidden');
        modalMensaje.textContent = 'Procesando...';
        modalAnimacion.innerHTML = `
            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
        `;
        modalAnimacion.classList.remove('hidden');

        fetch(`index.php?controller=usuario&action=cambiarEstado&id=${idUsuario}&estado=${estadoUsuario}`, {
            method: 'GET',
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                modalAnimacion.innerHTML = `
                    <svg class="h-16 w-16 text-green-500 mx-auto animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                `;
                modalMensaje.textContent = '¡Estado cambiado correctamente!';
                setTimeout(() => location.reload(), 1200);
            } else {
                modalAnimacion.innerHTML = `
                    <svg class="h-16 w-16 text-red-500 mx-auto animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
                modalMensaje.textContent = 'Error: ' + data.error;
                setTimeout(() => {
                    modalConfirmacion.classList.add('hidden');
                    resetModalConfirmacion();
                }, 1800);
            }
        })
        .catch(() => {
            modalAnimacion.innerHTML = `
                <svg class="h-16 w-16 text-red-500 mx-auto animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            `;
            modalMensaje.textContent = 'Ocurrió un error al cambiar el estado.';
            setTimeout(() => {
                modalConfirmacion.classList.add('hidden');
                resetModalConfirmacion();
            }, 1800);
        })
        .finally(() => {
            idUsuario = null;
            estadoUsuario = null;
        });
    });
});