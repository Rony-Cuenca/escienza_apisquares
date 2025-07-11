class SuperAdminController {
    constructor() {
        this.modals = {
            cliente: null,
            edicion: null
        };
        this.forms = {
            cliente: null,
            edicion: null
        };
        this.init();
    }

    init() {
        this.bindElements();
        this.bindEvents();
    }

    bindElements() {
        this.modals.cliente = document.getElementById('modalCliente');
        this.modals.edicion = document.getElementById('modalEdicion');
        this.forms.cliente = document.getElementById('formCliente');
        this.forms.edicion = document.getElementById('formEdicion');
    }

    bindEvents() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.cerrarTodosLosModales();
            }
        });

        this.bindModalEvents();
        this.bindFormEvents();
        this.bindRucEvents();
    }

    bindModalEvents() {
        if (this.modals.cliente) {
            this.modals.cliente.addEventListener('click', (e) => {
                if (e.target === this.modals.cliente) {
                    this.cerrarModalCliente();
                }
            });
        }

        if (this.modals.edicion) {
            this.modals.edicion.addEventListener('click', (e) => {
                if (e.target === this.modals.edicion) {
                    this.cerrarModalEdicion();
                }
            });
        }
    }
    bindFormEvents() {
        if (this.forms.cliente) {
            this.forms.cliente.addEventListener('submit', (e) => {
                e.preventDefault();
                this.validarYEnviarFormularioCliente();
            });
        }

        if (this.forms.edicion) {
            this.forms.edicion.addEventListener('submit', (e) => {
                e.preventDefault();
                this.validarYEnviarFormularioEdicion();
            });
        }
    }

    bindRucEvents() {
        const rucInput = document.getElementById('ruc');
        const btnConsultarRuc = document.getElementById('btnConsultarRuc');

        if (rucInput) {
            rucInput.addEventListener('input', (e) => {
                const ruc = e.target.value.replace(/\D/g, '');
                e.target.value = ruc.substring(0, 11);

                if (btnConsultarRuc) {
                    btnConsultarRuc.disabled = ruc.length !== 11;
                }
            });

            rucInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (btnConsultarRuc && !btnConsultarRuc.disabled) {
                        btnConsultarRuc.click();
                    }
                }
            });
        }

        if (btnConsultarRuc) {
            btnConsultarRuc.addEventListener('click', () => {
                const ruc = document.getElementById('ruc').value;
                if (ruc.length === 11) {
                    this.consultarRucBasico(ruc);
                } else {
                    this.mostrarError('Por favor ingrese un RUC válido de 11 dígitos');
                }
            });
        }
    }

    abrirModalCliente() {
        if (!this.modals.cliente) return;

        this.modals.cliente.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        this.limpiarFormulario();

        setTimeout(() => {
            const firstInput = this.modals.cliente.querySelector('input:not([type="hidden"])');
            if (firstInput) firstInput.focus();
        }, 100);
    }

    cerrarModalCliente() {
        if (!this.modals.cliente) return;

        this.modals.cliente.classList.add('hidden');
        document.body.style.overflow = 'auto';
        this.limpiarFormulario();
    }

    async abrirModalEdicion(id) {
        if (!id) {
            this.mostrarError('ID de cliente no válido');
            return;
        }

        try {
            const response = await fetch(`index.php?controller=cliente&action=obtenerDatos&id=${id}`);
            const data = await response.json();

            if (data.success) {
                this.llenarFormularioEdicion(data.data);
                this.modals.edicion.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                this.mostrarError(data.error || 'Error al cargar los datos del cliente');
            }
        } catch (error) {
            console.error('Error al cargar datos del cliente:', error);
            this.mostrarError('Error al cargar los datos del cliente');
        }
    }

    cerrarModalEdicion() {
        if (!this.modals.edicion) return;

        this.modals.edicion.classList.add('hidden');
        document.body.style.overflow = 'auto';
        this.limpiarMensajesEdicion();
    }

    cerrarTodosLosModales() {
        this.cerrarModalCliente();
        this.cerrarModalEdicion();
    }

    limpiarFormulario() {
        if (this.forms.cliente) {
            this.forms.cliente.reset();
        }
        this.limpiarMensajes();
    }

    llenarFormularioEdicion(data) {
        const campos = {
            'edit_id': data.id,
            'edit_ruc': data.ruc || '',
            'edit_razon_social': data.razon_social || '',
            'edit_email': data.correo || '',
            'edit_telefono': data.telefono || '',
            'edit_direccion': data.direccion || '',
            'edit_departamento': data.departamento || '',
            'edit_provincia': data.provincia || '',
            'edit_distrito': data.distrito || ''
        };

        Object.entries(campos).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.value = value;
            }
        });
    }

    validarYEnviarFormularioCliente() {
        const ruc = document.getElementById('ruc').value;
        const razonSocial = document.getElementById('razon_social').value;

        if (!this.validarRuc(ruc)) {
            this.mostrarError('Por favor ingrese un RUC válido de 11 dígitos');
            return;
        }

        if (!razonSocial.trim()) {
            this.mostrarError('Por favor ingrese la razón social');
            return;
        }

        this.forms.cliente.submit();
    }

    validarYEnviarFormularioEdicion() {
        const ruc = document.getElementById('edit_ruc').value;
        const razonSocial = document.getElementById('edit_razon_social').value;

        if (!this.validarRuc(ruc)) {
            this.mostrarErrorEdicion('Por favor ingrese un RUC válido de 11 dígitos');
            return;
        }

        if (!razonSocial.trim()) {
            this.mostrarErrorEdicion('Por favor ingrese la razón social');
            return;
        }

        this.forms.edicion.submit();
    }

    async cambiarEstadoCliente(id, nuevoEstado) {
        const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';

        try {
            const result = await Swal.fire({
                title: '¿Está seguro?',
                text: `¿Desea ${accion} este cliente?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado == 1 ? '#16a34a' : '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                const response = await fetch(`index.php?controller=cliente&action=cambiarEstado&id=${id}&estado=${nuevoEstado}`);
                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        title: '¡Éxito!',
                        text: `Cliente ${nuevoEstado == 1 ? 'activado' : 'desactivado'} correctamente`,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    location.reload();
                } else {
                    this.mostrarError(data.error || 'Error al cambiar el estado');
                }
            }
        } catch (error) {
            console.error('Error al cambiar estado:', error);
            this.mostrarError('Error al cambiar el estado del cliente');
        }
    }

    toggleEstablecimientos(clienteId) {
        const establecimientos = document.querySelectorAll(`.establecimientos-${clienteId}`);
        const arrow = document.getElementById(`arrow-${clienteId}`);

        if (establecimientos.length > 0) {
            const isHidden = establecimientos[0].classList.contains('hidden');

            establecimientos.forEach(row => {
                row.classList.toggle('hidden', !isHidden);
            });

            if (arrow) {
                arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }
    }

    async consultarRucBasico(ruc) {
        if (!this.validarRuc(ruc)) {
            this.mostrarError('Por favor ingrese un RUC válido de 11 dígitos');
            return;
        }

        const btnConsultar = document.getElementById('btnConsultarRuc');
        const originalHtml = btnConsultar.innerHTML;

        try {
            btnConsultar.disabled = true;
            btnConsultar.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';

            const response = await fetch(`index.php?controller=cliente&action=consultarRuc&ruc=${ruc}`);
            const data = await response.json();

            if (data.success) {
                this.llenarDatosBasicos(data);
                this.mostrarExito('Datos obtenidos correctamente');
            } else {
                this.mostrarError(data.error || 'No se pudieron obtener los datos');
            }
        } catch (error) {
            console.error('Error al consultar RUC:', error);
            this.mostrarError('Error al consultar el RUC');
        } finally {
            btnConsultar.disabled = false;
            btnConsultar.innerHTML = originalHtml;
        }
    }

    llenarDatosBasicos(data) {
        let datos = data.data || data.info_basica || data;

        const campos = {
            'razon_social': datos.nombre_o_razon_social || datos.razon_social,
            'direccion': datos.direccion,
            'departamento': datos.departamento,
            'provincia': datos.provincia,
            'distrito': datos.distrito,
            'telefono': datos.telefono
        };

        Object.entries(campos).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element && value) {
                element.value = value;
            }
        });
    }

    validarRuc(ruc) {
        return ruc && ruc.length === 11 && /^\d{11}$/.test(ruc);
    }

    mostrarExito(mensaje) {
        Swal.fire({
            title: '¡Éxito!',
            text: mensaje,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    mostrarError(mensaje) {
        Swal.fire({
            title: 'Error',
            text: mensaje,
            icon: 'error',
            confirmButtonColor: '#dc2626'
        });
    }

    mostrarErrorEdicion(mensaje) {
        Swal.fire({
            title: 'Error',
            text: mensaje,
            icon: 'error',
            confirmButtonColor: '#dc2626'
        });
    }

    limpiarMensajes() {
        const mensajesDiv = document.getElementById('mensajesModal');
        if (mensajesDiv) {
            mensajesDiv.classList.add('hidden');
            mensajesDiv.innerHTML = '';
        }
    }

    limpiarMensajesEdicion() {
        const mensajesDiv = document.getElementById('mensajesModalEdicion');
        if (mensajesDiv) {
            mensajesDiv.classList.add('hidden');
            mensajesDiv.innerHTML = '';
        }
    }
}


let superAdminController;

document.addEventListener('DOMContentLoaded', function () {
    superAdminController = new SuperAdminController();
});

function abrirModalCliente() {
    if (superAdminController) {
        superAdminController.abrirModalCliente();
    }
}

function cerrarModalCliente() {
    if (superAdminController) {
        superAdminController.cerrarModalCliente();
    }
}

function abrirModalEdicion(id) {
    if (superAdminController) {
        superAdminController.abrirModalEdicion(id);
    }
}

function cerrarModalEdicion() {
    if (superAdminController) {
        superAdminController.cerrarModalEdicion();
    }
}

function cambiarEstadoCliente(id, nuevoEstado) {
    if (superAdminController) {
        superAdminController.cambiarEstadoCliente(id, nuevoEstado);
    }
}

function toggleEstablecimientos(clienteId) {
    if (superAdminController) {
        superAdminController.toggleEstablecimientos(clienteId);
    }
}

function consultarRucBasico(ruc) {
    if (superAdminController) {
        superAdminController.consultarRucBasico(ruc);
    }
}
