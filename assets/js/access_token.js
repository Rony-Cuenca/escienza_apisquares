class AccessTokenModalController {
    constructor(config) {
        this.modal = document.getElementById(config.modalId);
        this.btnAbrir = document.getElementById(config.btnAbrirId);
        this.btnCerrar = document.getElementById(config.btnCerrarId);
        this.btnCancelar = document.getElementById(config.btnCancelarId);
        this.btnCopiar = document.getElementById(config.btnCopiarId);
        this.form = document.getElementById(config.formId);
        this.inputRol = document.getElementById(config.inputRolId);
        this.inputCodigo = document.getElementById(config.inputCodigoId);
        this.inputEstablecimiento = document.getElementById(config.inputEstablecimientoId);

        this._bindEvents();
    }

    _bindEvents() {
        this.btnAbrir.addEventListener('click', () => this.abrirModal());
        this.btnCerrar.addEventListener('click', () => this.cerrarModal());
        this.btnCancelar.addEventListener('click', () => this.cerrarModal());
        this.btnCopiar.addEventListener('click', () => this.copiarCodigo());

        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            const rol = this.inputRol.value;
            const idEstablecimiento = window.ID_ESTABLECIMIENTO_LOGUEADO;
            if (!rol || !idEstablecimiento) {
                Swal.fire('Error', 'Selecciona un rol válido.', 'error');
                return;
            }

            fetch('index.php?controller=accessToken&action=generar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    rol: rol
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.inputCodigo.value = data.codigo;
                        Swal.fire('¡Listo!', 'Código generado y guardado correctamente.', 'success');
                    } else {
                        Swal.fire('Error', data.error || 'No se pudo guardar el código.', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Ocurrió un error al guardar el código.', 'error');
                });
        });
    }

    abrirModal() {
        this.inputRol.value = '';
        this.inputCodigo.value = '';
        this.modal.classList.remove('hidden');
    }

    cerrarModal() {
        this.modal.classList.add('hidden');
    }

    copiarCodigo() {
        if (this.inputCodigo.value) {
            navigator.clipboard.writeText(this.inputCodigo.value);
            Swal.fire('Copiado', 'El código ha sido copiado al portapapeles.', 'success');
        }
    }

    generarCodigo(idEstablecimiento, rol) {
        const random = Math.floor(1000 + Math.random() * 9000);
        return `${idEstablecimiento}-${rol}-${random}`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new AccessTokenModalController({
        modalId: 'modalAccessToken',
        btnAbrirId: 'btnGenerarCodigo',
        btnCerrarId: 'btnCerrarModalAccessToken',
        btnCancelarId: 'btnCancelarModalAccessToken',
        btnCopiarId: 'btnCopiarCodigo',
        formId: 'formAccessToken',
        inputRolId: 'accessTokenRol',
        inputCodigoId: 'accessTokenCodigo',
        inputEstablecimientoId: 'accessTokenEstablecimiento'
    });
});
