class ModalCrudController {
    constructor(config) {
        this.modal = document.getElementById(config.modalId);
        this.titulo = document.getElementById(config.tituloId);
        this.form = document.getElementById(config.formId);
        this.btnNuevo = document.getElementById(config.btnNuevoId);
        this.btnCerrar = document.getElementById(config.btnCerrarId);
        this.btnCancelar = document.getElementById(config.btnCancelarId);
        this.inputs = {};
        for (const key in config.inputIds) {
            this.inputs[key] = document.getElementById(config.inputIds[key]);
        }
        this.errorUsuario = document.getElementById(config.errorUsuarioId) || this._crearErrorDiv(this.inputs.usuario, config.errorUsuarioId);
        this.errorCorreo = document.getElementById(config.errorCorreoId) || this._crearErrorDiv(this.inputs.correo, config.errorCorreoId);
        this.errorContrasena = document.getElementById(config.errorContrasenaId) || this._crearErrorDiv(this.inputs.contraseña, config.errorContrasenaId);
        this.cambiarContrasenaWrapper = document.getElementById(config.cambiarContrasenaWrapperId);
        this.checkCambiarContrasena = document.getElementById(config.checkCambiarContrasenaId);
        this.camposContrasena = document.getElementById(config.camposContrasenaId);
        this.sucursalOriginal = null;
        this._bindEvents(config);
        this.verificarAdmin();
    }

    _crearErrorDiv(input, id) {
        const div = document.createElement('div');
        div.id = id;
        div.className = 'text-red-600 text-sm mb-1 hidden';
        input.parentNode.appendChild(div);
        return div;
    }

    mostrarError(element, mensaje, input) {
        if (input) {
            input.classList.add('border-red-600', 'bg-red-50');
        }
        element.innerHTML = `
        <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>${mensaje}</span>
    `;
        element.classList.remove('hidden');
    }

    ocultarError(element, input) {
        if (input) {
            input.classList.remove('border-red-600', 'bg-red-50');
        }
        element.textContent = '';
        element.classList.add('hidden');
    }

    abrirModal({ titulo, action, values = {} }) {
        this.cerrarMenus();
        this.titulo.textContent = titulo;
        this.form.action = action;
        this.inputs.id_usuario.value = values.id || '';
        this.inputs.usuario.value = values.nombre || '';
        this.inputs.correo.value = values.correo || '';
        this.inputs.rol.value = values.rol || '';
        this.inputs.sucursal.value = values.sucursal || '';
        this.inputs.estado.value = values.estado || '1';
        this.inputs.contraseña.value = '';
        this.inputs.confirmar_contraseña.value = '';
        this.inputs.correo.readOnly = false;
        this.ocultarError(this.errorUsuario, this.inputs.usuario);
        this.ocultarError(this.errorCorreo, this.inputs.correo);
        this.ocultarError(this.errorContrasena, this.inputs.contraseña);

        const isEdicion = !!values.id;
        const sucursalSelect = this.inputs.sucursal;
        if (!isEdicion) {
            Array.from(sucursalSelect.options).forEach(opt => {
                if (opt.value && opt.value !== String(window.ID_SUCURSAL_LOGUEADO)) {
                    opt.style.display = 'none';
                } else {
                    opt.style.display = '';
                }
            });
            sucursalSelect.value = window.ID_SUCURSAL_LOGUEADO || '';
            sucursalSelect.disabled = true;
            sucursalSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
        } else {
            Array.from(sucursalSelect.options).forEach(opt => {
                opt.style.display = '';
            });
            sucursalSelect.disabled = false;
            sucursalSelect.classList.remove('bg-gray-100', 'cursor-not-allowed');
            this.sucursalOriginal = values.sucursal || '';
        }

        if (values.id && window.ID_USUARIO_LOGUEADO && String(values.id) === String(window.ID_USUARIO_LOGUEADO)) {
            this.inputs.sucursal.disabled = true;
            this.inputs.sucursal.classList.add('bg-gray-100', 'cursor-not-allowed');
        }

        if (values.id) {
            this.cambiarContrasenaWrapper.classList.remove('hidden');
            this.checkCambiarContrasena.checked = false;
            this.camposContrasena.classList.add('hidden');
            this.inputs.contraseña.required = false;
            this.inputs.confirmar_contraseña.required = false;
        } else {
            this.cambiarContrasenaWrapper.classList.add('hidden');
            this.camposContrasena.classList.remove('hidden');
            this.inputs.contraseña.required = true;
            this.inputs.confirmar_contraseña.required = true;
        }

        this.modal.classList.remove('hidden');
        setTimeout(() => this.inputs.usuario.focus(), 100);
    }

    cerrarModal() {
        this.modal.classList.add('hidden');
    }

    cerrarMenus() {
        document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
            const menu = btn.nextElementSibling;
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    }

    contieneTildes(texto) {
        return /[áéíóúÁÉÍÓÚ]/.test(texto);
    }

    validarFormulario(mostrarFocus = false) {
        this.ocultarError(this.errorUsuario, this.inputs.usuario);
        this.ocultarError(this.errorCorreo, this.inputs.correo);
        this.ocultarError(this.errorContrasena, this.inputs.contraseña);

        const usuario = this.inputs.usuario.value.trim();
        const correo = this.inputs.correo.value.trim();
        const rol = this.inputs.rol.value;
        const sucursal = this.inputs.sucursal.value;
        const pass = this.inputs.contraseña.value;
        const confirm = this.inputs.confirmar_contraseña.value;

        if (!usuario) {
            this.mostrarError(this.errorUsuario, 'El nombre de usuario es obligatorio', this.inputs.usuario);
            if (mostrarFocus) this.inputs.usuario.focus();
            return false;
        }
        if (this.contieneTildes(usuario)) {
            this.mostrarError(this.errorUsuario, 'El nombre de usuario no debe contener tildes', this.inputs.usuario);
            if (mostrarFocus) this.inputs.usuario.focus();
            return false;
        }
        if (!correo) {
            this.mostrarError(this.errorCorreo, 'El correo es obligatorio', this.inputs.correo);
            if (mostrarFocus) this.inputs.correo.focus();
            return false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
            this.mostrarError(this.errorCorreo, 'El correo no es válido', this.inputs.correo);
            if (mostrarFocus) this.inputs.correo.focus();
            return false;
        }
        if (!rol) {
            this.mostrarError(this.errorUsuario, 'El rol es obligatorio', this.inputs.rol);
            if (mostrarFocus) this.inputs.rol.focus();
            return false;
        }
        if (!sucursal) {
            this.mostrarError(this.errorUsuario, 'La sucursal es obligatoria', this.inputs.sucursal);
            if (mostrarFocus) this.inputs.sucursal.focus();
            return false;
        }

        if (!this.camposContrasena.classList.contains('hidden') && (pass || confirm)) {
            this.ocultarError(this.errorContrasena, this.inputs.contraseña);
            this.ocultarError(this.errorContrasena, this.inputs.confirmar_contraseña);

            if (pass !== confirm) {
                this.mostrarError(this.errorContrasena, 'Las contraseñas no coinciden', this.inputs.confirmar_contraseña);
                if (mostrarFocus) this.inputs.confirmar_contraseña.focus();
                return false;
            }
            if (pass.length < 8) {
                this.mostrarError(this.errorContrasena, 'La contraseña debe tener al menos 8 caracteres', this.inputs.contraseña);
                if (mostrarFocus) this.inputs.contraseña.focus();
                return false;
            }
            if (!/\d/.test(pass)) {
                this.mostrarError(this.errorContrasena, 'La contraseña debe contener al menos un número', this.inputs.contraseña);
                if (mostrarFocus) this.inputs.contraseña.focus();
                return false;
            }
            if (!/[A-Z]/.test(pass)) {
                this.mostrarError(this.errorContrasena, 'La contraseña debe contener al menos una mayúscula', this.inputs.contraseña);
                if (mostrarFocus) this.inputs.contraseña.focus();
                return false;
            }
            if (!/[\W_]/.test(pass)) {
                this.mostrarError(this.errorContrasena, 'La contraseña debe contener al menos un símbolo', this.inputs.contraseña);
                if (mostrarFocus) this.inputs.contraseña.focus();
                return false;
            }
            if (this.contieneTildes(pass)) {
                this.mostrarError(this.errorContrasena, 'La contraseña no debe contener tildes', this.inputs.contraseña);
                if (mostrarFocus) this.inputs.contraseña.focus();
                return false;
            }
        }

        return true;
    }

    verificarAdmin() {
        if (window.ROL_USUARIO_LOGUEADO !== 'Administrador') {
            const btnNuevo = document.getElementById('btnNuevoUsuario');
            if (btnNuevo) btnNuevo.style.display = 'none';
            document.querySelectorAll('[data-action="toggleMenu"]').forEach(btn => {
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.5';
                btn.setAttribute('aria-disabled', 'true');
                btn.title = 'Sin permisos';
            });
            document.querySelectorAll('[data-action="cambiarEstado"]').forEach(btn => {
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.5';
                btn.setAttribute('aria-disabled', 'true');
                btn.title = 'Sin permisos';
            });
        }
    }

    _bindEvents(config) {
        this.btnNuevo.addEventListener('click', () => {
            this.abrirModal({
                titulo: 'Nuevo Usuario',
                action: 'index.php?controller=usuario&action=crear'
            });
        });

        document.querySelectorAll('[data-action="editar"]').forEach((btn) => {
            btn.addEventListener('click', () => {
                this.abrirModal({
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

        this.btnCerrar.addEventListener('click', () => this.cerrarModal());
        this.btnCancelar.addEventListener('click', () => this.cerrarModal());

        // Checkbox para habilitar cambio de contraseña
        if (this.checkCambiarContrasena) {
            this.checkCambiarContrasena.addEventListener('change', () => {
                if (this.checkCambiarContrasena.checked) {
                    this.camposContrasena.classList.remove('hidden');
                    this.inputs.contraseña.required = true;
                    this.inputs.confirmar_contraseña.required = true;
                } else {
                    this.camposContrasena.classList.add('hidden');
                    this.inputs.contraseña.required = false;
                    this.inputs.confirmar_contraseña.required = false;
                    this.inputs.contraseña.value = '';
                    this.inputs.confirmar_contraseña.value = '';
                    this.ocultarError(this.errorContrasena, this.inputs.contraseña);
                }
            });
        }

        this.inputs.confirmar_contraseña.addEventListener('input', () => {
            this.ocultarError(this.errorContrasena, this.inputs.confirmar_contraseña);
            this.validarFormulario(false);
        });
        this.inputs.contraseña.addEventListener('input', () => {
            this.ocultarError(this.errorContrasena, this.inputs.contraseña);
            this.validarFormulario(false);
        });

        document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.cerrarMenus();
                const menu = btn.nextElementSibling;
                if (menu) menu.classList.toggle('hidden');
            });
        });
        document.addEventListener('click', () => this.cerrarMenus());

        document.querySelectorAll('[data-action="cambiarEstado"]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const idUsuario = btn.dataset.id;
                const estadoUsuario = btn.dataset.estado;
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

        this.form.addEventListener('submit', (e) => {
            if (this.sucursalOriginal && this.inputs.sucursal.value !== this.sucursalOriginal) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Estás cambiando la sucursal de este usuario. ¿Deseas continuar?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.sucursalOriginal = null;
                        this.form.submit();
                    }
                });
                return false;
            }

            e.preventDefault();
            this.ocultarError(this.errorUsuario, this.inputs.usuario);
            this.ocultarError(this.errorCorreo, this.inputs.correo);
            this.ocultarError(this.errorContrasena, this.inputs.contraseña);

            if (!this.validarFormulario()) return;

            const usuario = this.inputs.usuario.value.trim();
            const correo = this.inputs.correo.value.trim();
            const id_usuario = this.inputs.id_usuario.value || 0;

            fetch(`index.php?controller=usuario&action=verificarUsuario&usuario=${encodeURIComponent(usuario)}&id_usuario=${id_usuario}`)
                .then(res => res.json())
                .then(data => {
                    if (data.existe) {
                        this.mostrarError(this.errorUsuario, 'El nombre de usuario ya existe', this.inputs.usuario);
                        this.inputs.usuario.focus();
                        return false;
                    } else {
                        fetch(`index.php?controller=usuario&action=verificarCorreo&correo=${encodeURIComponent(correo)}&id_usuario=${id_usuario}`)
                            .then(res => res.json())
                            .then(dataCorreo => {
                                if (dataCorreo.existe) {
                                    this.mostrarError(this.errorCorreo, 'El correo ya está registrado', this.inputs.correo);
                                    this.inputs.correo.focus();
                                    return false;
                                } else {
                                    this.form.submit();
                                }
                            })
                            .catch(() => {
                                this.mostrarError(this.errorCorreo, 'No se pudo verificar el correo');
                            });
                    }
                })
                .catch(() => {
                    this.mostrarError(this.errorUsuario, 'No se pudo verificar el usuario');
                });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ModalCrudController({
        modalId: 'modalUsuario',
        tituloId: 'modalTitulo',
        formId: 'formUsuario',
        btnNuevoId: 'btnNuevoUsuario',
        btnCerrarId: 'btnCerrarModal',
        btnCancelarId: 'btnCancelarModal',
        inputIds: {
            id_usuario: 'modalIdUsuario',
            usuario: 'modalUsuarioNombre',
            correo: 'modalUsuarioCorreo',
            rol: 'modalUsuarioRol',
            sucursal: 'modalUsuarioSucursal',
            contraseña: 'modalUsuarioContraseña',
            confirmar_contraseña: 'modalUsuarioConfirmarContraseña',
            estado: 'modalUsuarioEstado'
        },
        errorUsuarioId: 'errorUsuario',
        errorCorreoId: 'errorCorreo',
        errorContrasenaId: 'errorContrasena',
        cambiarContrasenaWrapperId: 'cambiarContrasenaWrapper',
        checkCambiarContrasenaId: 'checkCambiarContrasena',
        camposContrasenaId: 'camposContrasena'
    });
});