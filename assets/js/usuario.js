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
        this.errorEstablecimiento = document.getElementById(config.errorEstablecimientoId) || this._crearErrorDiv(this.inputs.establecimiento, config.errorEstablecimientoId);
        this.cambiarContrasenaWrapper = document.getElementById(config.cambiarContrasenaWrapperId);
        this.checkCambiarContrasena = document.getElementById(config.checkCambiarContrasenaId);
        this.camposContrasena = document.getElementById(config.camposContrasenaId);
        this.establecimientoOriginal = null;
        this._bindEvents(config);
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
        this.inputs.establecimiento.value = values.establecimiento || '';
        this.inputs.estado.value = values.estado || '1';
        this.inputs.contraseña.value = '';
        this.inputs.confirmar_contraseña.value = '';
        this.inputs.correo.readOnly = false;

        this.ocultarError(this.errorUsuario, this.inputs.usuario);
        this.ocultarError(this.errorCorreo, this.inputs.correo);
        this.ocultarError(this.errorContrasena, this.inputs.contraseña);
        this.ocultarError(this.errorEstablecimiento, this.inputs.establecimiento);

        const isEdicion = !!values.id;
        this.establecimientoOriginal = values.establecimiento || '';

        // Configurar permisos según el usuario
        if (values.id && window.ID_USUARIO_LOGUEADO && String(values.id) === String(window.ID_USUARIO_LOGUEADO)) {
            this.inputs.rol.disabled = true;
            this.inputs.establecimiento.disabled = true;
            
            // Agregar campos hidden para asegurar que los valores se envíen
            let hiddenRol = document.querySelector('input[name="rol_hidden"]');
            if (!hiddenRol) {
                hiddenRol = document.createElement('input');
                hiddenRol.type = 'hidden';
                hiddenRol.name = 'rol_hidden';
                this.form.appendChild(hiddenRol);
            }
            hiddenRol.value = this.inputs.rol.value;
            
            let hiddenEstablecimiento = document.querySelector('input[name="id_establecimiento_hidden"]');
            if (!hiddenEstablecimiento) {
                hiddenEstablecimiento = document.createElement('input');
                hiddenEstablecimiento.type = 'hidden';
                hiddenEstablecimiento.name = 'id_establecimiento_hidden';
                this.form.appendChild(hiddenEstablecimiento);
            }
            hiddenEstablecimiento.value = this.inputs.establecimiento.value;
            
            // Asegurar que el estado se mantenga
            let hiddenEstado = document.querySelector('input[name="estado_hidden"]');
            if (!hiddenEstado) {
                hiddenEstado = document.createElement('input');
                hiddenEstado.type = 'hidden';
                hiddenEstado.name = 'estado_hidden';
                this.form.appendChild(hiddenEstado);
            }
            hiddenEstado.value = this.inputs.estado.value || '1';
        } else {
            this.inputs.rol.disabled = false;
            this.inputs.establecimiento.disabled = false;
            
            // Eliminar campos hidden si existen
            const hiddenRol = document.querySelector('input[name="rol_hidden"]');
            const hiddenEstablecimiento = document.querySelector('input[name="id_establecimiento_hidden"]');
            const hiddenEstado = document.querySelector('input[name="estado_hidden"]');
            if (hiddenRol) hiddenRol.remove();
            if (hiddenEstablecimiento) hiddenEstablecimiento.remove();
            if (hiddenEstado) hiddenEstado.remove();
        }

        // Configurar campos de contraseña
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
        this.ocultarError(this.errorEstablecimiento, this.inputs.establecimiento);

        const usuario = this.inputs.usuario.value.trim();
        const correo = this.inputs.correo.value.trim();
        const rol = this.inputs.rol.value;
        const establecimiento = this.inputs.establecimiento.value;
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
        if (!establecimiento) {
            this.mostrarError(this.errorEstablecimiento, 'El establecimiento es obligatorio', this.inputs.establecimiento);
            if (mostrarFocus) this.inputs.establecimiento.focus();
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
                        establecimiento: btn.dataset.establecimiento,
                        establecimiento_nombre: btn.closest('tr').querySelector('td:nth-child(5)').textContent.trim(),
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
                if (menu) {
                    menu.classList.toggle('hidden');
                    menu.style.left = '';
                    menu.style.right = '';
                    const rect = menu.getBoundingClientRect();
                    if (rect.right > window.innerWidth) {
                        menu.style.right = 'auto';
                        menu.style.left = '0';
                    } else {
                        menu.style.right = '0';
                        menu.style.left = 'auto';
                    }
                    if (window.innerWidth < 640) {
                        menu.style.minWidth = '60vw';
                    } else {
                        menu.style.minWidth = '120px';
                    }
                }
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

                // Determinar la acción basada en el estado objetivo
                let textoAccion, textoEstado;
                
                if (estadoUsuario === '1') {
                    // Cambiar a activo
                    textoAccion = 'activar';
                    textoEstado = 'activó';
                } else if (estadoUsuario === '2') {
                    // Cambiar a inactivo
                    textoAccion = 'desactivar';
                    textoEstado = 'desactivó';
                } else if (estadoUsuario === '3') {
                    // Cambiar a deshabilitado
                    textoAccion = 'deshabilitar';
                    textoEstado = 'deshabilitó';
                } else {
                    textoAccion = 'cambiar el estado de';
                    textoEstado = 'cambió el estado de';
                }

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Deseas ${textoAccion} este usuario?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0018F4',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Sí, ${textoAccion}`,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`index.php?controller=usuario&action=cambiarEstado&id=${idUsuario}&estado=${estadoUsuario}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        '¡Listo!', 
                                        `El usuario se ${textoEstado} correctamente.`, 
                                        'success'
                                    ).then(() => location.reload());
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
            const establecimientoActual = this.inputs.establecimiento.value;

            if (this.establecimientoOriginal && establecimientoActual !== this.establecimientoOriginal) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Estás cambiando el establecimiento de este usuario. ¿Deseas continuar?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.establecimientoOriginal = null;
                        this.form.submit();
                    }
                });
                return false;
            }

            e.preventDefault();
            this.ocultarError(this.errorUsuario, this.inputs.usuario);
            this.ocultarError(this.errorCorreo, this.inputs.correo);
            this.ocultarError(this.errorContrasena, this.inputs.contraseña);
            this.ocultarError(this.errorEstablecimiento, this.inputs.establecimiento);

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
    console.log('🔍 DEBUGGING: DOMContentLoaded fired');
    console.log('🔍 DEBUGGING: ROL_USUARIO_LOGUEADO =', window.ROL_USUARIO_LOGUEADO);
    
    const btnNuevo = document.getElementById('btnNuevoUsuario');
    console.log('🔍 DEBUGGING: btnNuevoUsuario found =', !!btnNuevo);
    if (btnNuevo) {
        console.log('🔍 DEBUGGING: btnNuevo display =', btnNuevo.style.display);
        console.log('🔍 DEBUGGING: btnNuevo visible =', btnNuevo.offsetParent !== null);
    }
    
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
            establecimiento: 'modalUsuarioEstablecimiento',
            contraseña: 'modalUsuarioContraseña',
            confirmar_contraseña: 'modalUsuarioConfirmarContraseña',
            estado: 'modalUsuarioEstado'
        },
        errorUsuarioId: 'errorUsuario',
        errorCorreoId: 'errorCorreo',
        errorContrasenaId: 'errorContrasena',
        errorEstablecimientoId: 'errorEstablecimiento',
        cambiarContrasenaWrapperId: 'cambiarContrasenaWrapper',
        checkCambiarContrasenaId: 'checkCambiarContrasena',
        camposContrasenaId: 'camposContrasena'
    });
});
