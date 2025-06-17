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
        this.errorDiv = document.getElementById(config.errorId) || this._crearErrorDiv(this.inputs.ruc, config.errorId);

        this._bindEvents(config);
    }

    _crearErrorDiv(input, id) {
        const div = document.createElement('div');
        div.id = id;
        div.className = 'text-red-600 text-sm mb-1 hidden';
        input.parentNode.appendChild(div);
        return div;
    }

    mostrarError(mensaje) {
        this.inputs.ruc.classList.add('border-red-600', 'bg-red-50');
        this.errorDiv.innerHTML = `
        <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>${mensaje}</span>
    `;
        this.errorDiv.classList.remove('hidden');
    }

    ocultarError() {
        this.inputs.ruc.classList.remove('border-red-600', 'bg-red-50');
        this.errorDiv.textContent = '';
        this.errorDiv.classList.add('hidden');
    }

    abrirModal({ titulo, action, values = {} }) {
        this.titulo.textContent = titulo;
        this.form.action = action;
        this.inputs.id.value = values.id || '';
        this.inputs.ruc.value = values.ruc || '';
        this.inputs.razon_social.value = values.razon_social || '';
        this.inputs.direccion.value = values.direccion || '';
        this.inputs.estado.value = values.estado || '1';
        this.ocultarError();
        this.modal.classList.remove('hidden');
        setTimeout(() => this.inputs.ruc.focus(), 100);
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

    _bindEvents(config) {
        this.btnNuevo.addEventListener('click', () => {
            this.abrirModal({
                titulo: 'Nuevo Establecimiento',
                action: 'index.php?controller=establecimiento&action=crear'
            });
        });

        document.querySelectorAll('[data-action="editar"]').forEach((btn) => {
            btn.addEventListener('click', () => {
                this.abrirModal({
                    titulo: 'Editar Establecimiento',
                    action: 'index.php?controller=establecimiento&action=editar',
                    values: {
                        id: btn.dataset.id,
                        ruc: btn.dataset.ruc,
                        razon_social: btn.dataset.razon_social,
                        direccion: btn.dataset.direccion,
                        estado: btn.dataset.estado
                    }
                });
            });
        });

        this.btnCerrar.addEventListener('click', () => this.cerrarModal());
        this.btnCancelar.addEventListener('click', () => this.cerrarModal());

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
                const idEstablecimiento = btn.dataset.id;
                const estadoEstablecimiento = btn.dataset.estado;
                if (!idEstablecimiento || !estadoEstablecimiento) {
                    Swal.fire('Error', 'Datos inválidos para cambiar el estado.', 'error');
                    return;
                }
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Deseas cambiar el estado de este establecimiento?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0018F4',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`index.php?controller=establecimiento&action=cambiarEstado&id=${idEstablecimiento}&estado=${estadoEstablecimiento}`)
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
            e.preventDefault();
            this.ocultarError();

            const ruc = this.inputs.ruc.value.trim();
            const razon = this.inputs.razon_social.value.trim();
            const direccion = this.inputs.direccion.value.trim();
            const id = this.inputs.id.value || 0;

            if (!ruc.match(/^\d{11}$/)) {
                this.mostrarError('El RUC debe tener 11 dígitos numéricos.');
                this.inputs.ruc.focus();
                return false;
            }
            if (!razon) {
                this.inputs.razon_social.focus();
                return false;
            }
            if (!direccion) {
                this.inputs.direccion.focus();
                return false;
            }

            fetch(`index.php?controller=establecimiento&action=verificarRuc&ruc=${encodeURIComponent(ruc)}&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.existe) {
                        this.mostrarError('El RUC ya existe.');
                        this.inputs.ruc.focus();
                        return false;
                    } else {
                        this.form.submit();
                    }
                })
                .catch(() => {
                    this.mostrarError('No se pudo verificar el RUC');
                });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ModalCrudController({
        modalId: 'modalEstablecimiento',
        tituloId: 'modalEstTitulo',
        formId: 'formEstablecimiento',
        btnNuevoId: 'btnNuevoEstablecimiento',
        btnCerrarId: 'btnCerrarModalEst',
        btnCancelarId: 'btnCancelarModalEst',
        inputIds: {
            id: 'modalEstId',
            ruc: 'modalEstRuc',
            razon_social: 'modalEstRazon',
            direccion: 'modalEstDireccion',
            estado: 'modalEstEstado'
        },
        errorId: 'errorRuc'
    });
});