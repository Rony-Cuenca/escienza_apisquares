document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalUsuario');
    const modalTitulo = document.getElementById('modalTitulo');
    const btnNuevoUsuario = document.getElementById('btnNuevoUsuario');
    const btnCerrarModal = document.getElementById('btnCerrarModal');
    const btnCancelarModal = document.getElementById('btnCancelarModal');
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

    const cambiarContrasenaWrapper = document.getElementById('cambiarContrasenaWrapper');
    const checkCambiarContrasena = document.getElementById('checkCambiarContrasena');
    const camposContrasena = document.getElementById('camposContrasena');

    let idUsuario = null;
    let estadoUsuario = null;

    const errorContrasena = document.getElementById('errorContrasena');
    let errorUsuario = document.getElementById('errorUsuario');
    if (!errorUsuario) {
        errorUsuario = document.createElement('div');
        errorUsuario.id = 'errorUsuario';
        errorUsuario.className = 'text-red-600 text-sm mb-1 hidden';
        modalInputs.nombre.parentNode.appendChild(errorUsuario);
    }

    let errorCorreo = document.getElementById('errorCorreo');
    if (!errorCorreo) {
        errorCorreo = document.createElement('div');
        errorCorreo.id = 'errorCorreo';
        errorCorreo.className = 'text-red-600 text-sm mb-1 hidden';
        modalInputs.correo.parentNode.appendChild(errorCorreo);
    }

    function mostrarError(element, mensaje) {
        element.textContent = mensaje;
        element.classList.remove('hidden');
    }
    function ocultarError(element) {
        element.textContent = '';
        element.classList.add('hidden');
    }

    function contieneTildes(texto) {
        return /[áéíóúÁÉÍÓÚ]/.test(texto);
    }

    function validarFormularioUsuario() {
        ocultarError(errorContrasena);
        ocultarError(errorUsuario);
        ocultarError(errorCorreo);

        const usuario = modalInputs.nombre.value.trim();
        const correo = modalInputs.correo.value.trim();
        const pass = modalInputs.contraseña.value;
        const confirm = modalInputs.confirmarContraseña.value;

        if (contieneTildes(usuario)) {
            mostrarError(errorUsuario, 'El nombre de usuario no debe contener tildes');
            modalInputs.nombre.focus();
            return false;
        }

        if (!correo) {
            mostrarError(errorCorreo, 'El correo es obligatorio');
            modalInputs.correo.focus();
            return false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
            mostrarError(errorCorreo, 'El correo no es válido');
            modalInputs.correo.focus();
            return false;
        }

        if (!camposContrasena.classList.contains('hidden') && (pass || confirm)) {
            if (pass !== confirm) {
                mostrarError(errorContrasena, 'Las contraseñas no coinciden');
                modalInputs.confirmarContraseña.focus();
                return false;
            }
            if (pass.length < 8) {
                mostrarError(errorContrasena, 'La contraseña debe tener al menos 8 caracteres');
                modalInputs.contraseña.focus();
                return false;
            }
            if (!/\d/.test(pass)) {
                mostrarError(errorContrasena, 'La contraseña debe contener al menos un número');
                modalInputs.contraseña.focus();
                return false;
            }
            if (!/[A-Z]/.test(pass)) {
                mostrarError(errorContrasena, 'La contraseña debe contener al menos una mayúscula');
                modalInputs.contraseña.focus();
                return false;
            }
            if (!/[\W_]/.test(pass)) {
                mostrarError(errorContrasena, 'La contraseña debe contener al menos un símbolo');
                modalInputs.contraseña.focus();
                return false;
            }
            if (contieneTildes(pass)) {
                mostrarError(errorContrasena, 'La contraseña no debe contener tildes');
                modalInputs.contraseña.focus();
                return false;
            }
        }

        return true;
    }

    function validarContrasenas() {
        ocultarError(errorContrasena);
        const pass = modalInputs.contraseña.value;
        const confirm = modalInputs.confirmarContraseña.value;
        if (confirm && pass !== confirm) {
            mostrarError(errorContrasena, 'Las contraseñas no coinciden');
            return false;
        }
        return true;
    }

    modalInputs.confirmarContraseña.addEventListener('input', validarContrasenas);
    modalInputs.contraseña.addEventListener('input', validarContrasenas);

    // Checkbox para habilitar cambio de contraseña
    if (checkCambiarContrasena) {
        checkCambiarContrasena.addEventListener('change', function () {
            if (this.checked) {
                camposContrasena.classList.remove('hidden');
                modalInputs.contraseña.required = true;
                modalInputs.confirmarContraseña.required = true;
            } else {
                camposContrasena.classList.add('hidden');
                modalInputs.contraseña.required = false;
                modalInputs.confirmarContraseña.required = false;
                modalInputs.contraseña.value = '';
                modalInputs.confirmarContraseña.value = '';
                ocultarError(errorContrasena);
            }
        });
    }

    formUsuario.addEventListener('submit', function (e) {
        e.preventDefault();
        ocultarError(errorContrasena);
        ocultarError(errorUsuario);
        ocultarError(errorCorreo);

        if (!validarFormularioUsuario()) return;

        const usuario = modalInputs.nombre.value.trim();
        const correo = modalInputs.correo.value.trim();
        const id_usuario = modalInputs.id.value || 0;

        fetch(`index.php?controller=usuario&action=verificarUsuario&usuario=${encodeURIComponent(usuario)}&id_usuario=${id_usuario}`)
            .then(res => res.json())
            .then(data => {
                if (data.existe) {
                    mostrarError(errorUsuario, 'El nombre de usuario ya existe');
                    modalInputs.nombre.focus();
                    return false;
                } else {
                    fetch(`index.php?controller=usuario&action=verificarCorreo&correo=${encodeURIComponent(correo)}&id_usuario=${id_usuario}`)
                        .then(res => res.json())
                        .then(dataCorreo => {
                            if (dataCorreo.existe) {
                                mostrarError(errorCorreo, 'El correo ya está registrado');
                                modalInputs.correo.focus();
                                return false;
                            } else {
                                formUsuario.submit();
                            }
                        })
                        .catch(() => {
                            mostrarError(errorCorreo, 'No se pudo verificar el correo');
                        });
                }
            })
            .catch(() => {
                mostrarError(errorUsuario, 'No se pudo verificar el usuario');
            });
    });

    btnNuevoUsuario.addEventListener('click', () => {
        abrirModalUsuario({ titulo: 'Nuevo Usuario', action: 'index.php?controller=usuario&action=crear' });
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

    function abrirModalUsuario({ titulo, action, values = {} }) {
        cerrarMenus();
        modalTitulo.textContent = titulo;
        formUsuario.action = action;
        modalInputs.id.value = values.id || '';
        modalInputs.nombre.value = values.nombre || '';
        modalInputs.correo.value = values.correo || '';
        modalInputs.correo.readOnly = false;
        modalInputs.rol.value = values.rol || '';
        modalInputs.sucursal.value = values.sucursal || '';
        modalInputs.estado.value = values.estado || '1';
        modalInputs.contraseña.value = '';
        modalInputs.confirmarContraseña.value = '';
        ocultarError(errorContrasena);
        ocultarError(errorUsuario);
        ocultarError(errorCorreo);

        if (values.id) {
            cambiarContrasenaWrapper.classList.remove('hidden');
            checkCambiarContrasena.checked = false;
            camposContrasena.classList.add('hidden');
            modalInputs.contraseña.required = false;
            modalInputs.confirmarContraseña.required = false;
        } else {
            cambiarContrasenaWrapper.classList.add('hidden');
            camposContrasena.classList.remove('hidden');
            modalInputs.contraseña.required = true;
            modalInputs.confirmarContraseña.required = true;
        }

        modal.classList.remove('hidden');
        setTimeout(() => modalInputs.nombre.focus(), 100);
    }

    function cerrarModalUsuario() {
        modal.classList.add('hidden');
    }

    btnCerrarModal.addEventListener('click', cerrarModalUsuario);
    btnCancelarModal && btnCancelarModal.addEventListener('click', cerrarModalUsuario);

    function cerrarMenus() {
        document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
            const menu = btn.nextElementSibling;
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    }

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
                Swal.fire('Error', 'Datos inválidos para cambiar el estado.', 'error');
                return;
            }
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas cambiar el estado de este usuario?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0018F4',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`index.php?controller=usuario&action=cambiarEstado&id=${idUsuario}&estado=${estadoUsuario}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('¡Listo!', 'El estado se cambió correctamente.', 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', data.error || 'No se pudo cambiar el estado.', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Ocurrió un error al cambiar el estado.', 'error');
                        });
                }
            });
        });
    });
});