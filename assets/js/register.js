class RegisterController {
    constructor(config) {
        this.stepAccessToken = document.getElementById(config.stepAccessTokenId);
        this.form = document.getElementById(config.formId);
        this.btnVerificarToken = document.getElementById(config.btnVerificarTokenId);
        this.inputToken = document.getElementById(config.inputTokenId);
        this.feedback = document.getElementById(config.feedbackId);
        this.inputs = {};
        for (const key in config.inputIds) {
            this.inputs[key] = document.getElementById(config.inputIds[key]);
        }
        this.errorCorreo = this._crearErrorDiv(this.inputs.correo, 'errorRegisterCorreo');
        this.errorUsuario = this._crearErrorDiv(this.inputs.usuario, 'errorRegisterUsuario');
        this.errorContrasena = document.getElementById(config.errorContrasenaId) || this._crearErrorDiv(this.inputs.confirmar_contrasena, config.errorContrasenaId);

        this._bindEvents();
    }

    _crearErrorDiv(input, id) {
        let div = document.getElementById(id);
        if (!div) {
            div = document.createElement('div');
            div.id = id;
            div.className = 'text-red-600 text-sm mb-1 hidden flex items-center gap-1 pt-1';
            input.parentNode.appendChild(div);
        }
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

    validarUsuario() {
        const usuario = this.inputs.usuario.value.trim();
        this.ocultarError(this.errorUsuario, this.inputs.usuario);
        if (!usuario) {
            this.mostrarError(this.errorUsuario, 'El nombre de usuario es obligatorio', this.inputs.usuario);
            return false;
        }
        if (/[áéíóúÁÉÍÓÚ]/.test(usuario)) {
            this.mostrarError(this.errorUsuario, 'El nombre de usuario no debe contener tildes', this.inputs.usuario);
            return false;
        }
        return true;
    }

    validarCorreo() {
        const correo = this.inputs.correo.value.trim();
        this.ocultarError(this.errorCorreo, this.inputs.correo);
        if (!correo) {
            this.mostrarError(this.errorCorreo, 'El correo es obligatorio', this.inputs.correo);
            return false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
            this.mostrarError(this.errorCorreo, 'El correo no es válido', this.inputs.correo);
            return false;
        }
        return true;
    }

    validarPasswords() {
        this.ocultarError(this.errorContrasena, this.inputs.contrasena);
        this.ocultarError(this.errorContrasena, this.inputs.confirmar_contrasena);
        const pass = this.inputs.contrasena.value;
        const confirm = this.inputs.confirmar_contrasena.value;
        if (pass && confirm && pass !== confirm) {
            this.mostrarError(this.errorContrasena, 'Las contraseñas no coinciden', this.inputs.confirmar_contrasena);
            return false;
        }
        return true;
    }

    _bindEvents() {
        this.btnVerificarToken.addEventListener('click', () => {
            const token = this.inputToken.value.trim();
            this.feedback.textContent = '';
            if (!token) {
                this.feedback.textContent = 'Debes ingresar un código de acceso.';
                this.feedback.className = 'text-red-600 mt-2';
                return;
            }
            fetch('index.php?controller=accessToken&action=validar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'hashcode=' + encodeURIComponent(token)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.feedback.innerHTML = '<span style="color:green;">✔ Código válido</span>';
                        this.inputs.id_establecimiento.value = data.token.id_establecimiento;
                        this.inputs.rol.value = data.token.rol;
                        this.inputs.access_token.value = token;
                        this.inputs.establecimiento_nombre.value = data.nombre_establecimiento || data.token.id_establecimiento;
                        this.inputs.rol_nombre.value = data.token.rol;
                        this.form.classList.remove('hidden');
                        this.stepAccessToken.classList.add('hidden');
                    } else {
                        this.feedback.innerHTML = '<span style="color:red;">✖ ' + (data.error || 'Código inválido') + '</span>';
                    }
                })
                .catch(() => {
                    this.feedback.innerHTML = '<span style="color:red;">✖ Error al verificar el código</span>';
                });
        });

        // Solo validación de contraseñas en tiempo real
        this.inputs.contrasena.addEventListener('input', () => {
            this.ocultarError(this.errorContrasena, this.inputs.contrasena);
            this.validarPasswords();
        });
        this.inputs.confirmar_contrasena.addEventListener('input', () => {
            this.ocultarError(this.errorContrasena, this.inputs.confirmar_contrasena);
            this.validarPasswords();
        });

        this.form.addEventListener('submit', (e) => {
            this.ocultarError(this.errorContrasena, this.inputs.contrasena);
            this.ocultarError(this.errorContrasena, this.inputs.confirmar_contrasena);
            this.ocultarError(this.errorCorreo, this.inputs.correo);
            this.ocultarError(this.errorUsuario, this.inputs.usuario);

            if (!this.validarUsuario() || !this.validarCorreo() || !this.validarPasswords()) {
                e.preventDefault();
                return false;
            }

            const usuario = this.inputs.usuario.value.trim();
            const correo = this.inputs.correo.value.trim();

            // Verifica usuario y correo únicos solo al enviar
            Promise.all([
                fetch(`index.php?controller=usuario&action=verificarUsuario&usuario=${encodeURIComponent(usuario)}`).then(res => res.json()),
                fetch(`index.php?controller=usuario&action=verificarCorreo&correo=${encodeURIComponent(correo)}`).then(res => res.json())
            ]).then(([dataUsuario, dataCorreo]) => {
                if (dataUsuario.existe) {
                    e.preventDefault();
                    this.mostrarError(this.errorUsuario, 'El nombre de usuario ya existe', this.inputs.usuario);
                    this.inputs.usuario.focus();
                    document.getElementById('btnRegister').disabled = false;
                    return false;
                }
                if (dataCorreo.existe) {
                    e.preventDefault();
                    this.mostrarError(this.errorCorreo, 'El correo ya está registrado', this.inputs.correo);
                    this.inputs.correo.focus();
                    document.getElementById('btnRegister').disabled = false;
                    return false;
                }
                document.getElementById('btnRegister').disabled = true;
                this.form.submit();
            }).catch(() => {
                e.preventDefault();
                this.mostrarError(this.errorCorreo, 'No se pudo verificar usuario/correo', this.inputs.correo);
                document.getElementById('btnRegister').disabled = false;
            });

            e.preventDefault();
        });

        const errorDiv = document.querySelector('.mb-4.text-red-600');
        if (errorDiv) {
            this.form.querySelectorAll('input,select').forEach(el => {
                el.addEventListener('input', () => {
                    errorDiv.style.display = 'none';
                });
            });
            this.btnVerificarToken.addEventListener('click', () => {
                errorDiv.style.display = 'none';
            });
        }

        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = btn.querySelector('svg');
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.add('text-[#0018F4]');
                } else {
                    input.type = "password";
                    icon.classList.remove('text-[#0018F4]');
                }
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new RegisterController({
        stepAccessTokenId: 'stepAccessToken',
        formId: 'formRegister',
        btnVerificarTokenId: 'btnVerificarToken',
        inputTokenId: 'inputAccessToken',
        feedbackId: 'accessTokenFeedback',
        inputIds: {
            id_establecimiento: 'reg_id_establecimiento',
            rol: 'reg_rol',
            access_token: 'reg_access_token',
            establecimiento_nombre: 'reg_establecimiento_nombre',
            rol_nombre: 'reg_rol_nombre',
            usuario: 'reg_usuario',
            correo: 'reg_correo',
            contrasena: 'reg_contrasena',
            confirmar_contrasena: 'reg_confirmar_contrasena'
        },
        errorContrasenaId: 'errorRegisterContrasena'
    });
});