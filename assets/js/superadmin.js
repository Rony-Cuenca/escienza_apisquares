function entrarEstablecimientoSweet(idCliente, idEstablecimiento) {
    Swal.fire({
        title: '¿Entrar al establecimiento?',
        text: '¿Desea acceder a este establecimiento? Esta acción cambiará el contexto de trabajo.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, entrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?controller=superadmin&action=accesoDirectoEstablecimiento&id_cliente=${idCliente}&id_establecimiento=${idEstablecimiento}`;
        }
    });
}

async function cambiarEstadoEstablecimiento(id, nuevoEstado) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    try {
        const result = await Swal.fire({
            title: '¿Está seguro?',
            text: `¿Desea ${accion} este establecimiento?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: nuevoEstado == 1 ? '#16a34a' : '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar'
        });
        if (result.isConfirmed) {
            const response = await fetch(`index.php?controller=cliente&action=cambiarEstadoEstablecimiento&id=${id}&estado=${nuevoEstado}`);
            const data = await response.json();
            if (data.success) {
                await Swal.fire({
                    title: '¡Éxito!',
                    text: data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                location.reload();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.error || 'Error al cambiar el estado',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        }
    } catch (error) {
        Swal.fire({
            title: 'Error',
            text: 'Error al cambiar el estado del establecimiento',
            icon: 'error',
            confirmButtonColor: '#dc2626'
        });
    }
}
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

        this.resetearIndicadoresVisuales();
        this.limpiarMensajes();
    }

    resetearIndicadoresVisuales() {
        const estadoDiv = document.getElementById('estadoContribuyente');
        const condicionDiv = document.getElementById('condicionContribuyente');

        if (estadoDiv) {
            estadoDiv.className = 'flex items-center px-4 py-3 bg-gray-50 rounded-lg border border-gray-200';
            estadoDiv.innerHTML = `
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                <span class="text-gray-500 font-medium">No consultado</span>
            </div>
        `;
        }

        if (condicionDiv) {
            condicionDiv.className = 'flex items-center px-4 py-3 bg-gray-50 rounded-lg border border-gray-200';
            condicionDiv.innerHTML = `
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                <span class="text-gray-500 font-medium">No consultado</span>
            </div>
        `;
        }
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

        this.actualizarEstadoContribuyente(datos.estado || 'ACTIVO');
        this.actualizarCondicionContribuyente(datos.condicion || 'HABIDO');
    }

    actualizarEstadoContribuyente(estado) {
        const estadoDiv = document.getElementById('estadoContribuyente');
        if (!estadoDiv) return;

        const esActivo = estado.toUpperCase() === 'ACTIVO';
        const colorClass = esActivo ? 'bg-green-500' : 'bg-red-500';
        const bgClass = esActivo ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
        const textClass = esActivo ? 'text-green-800' : 'text-red-800';

        estadoDiv.className = `flex items-center px-4 py-3 rounded-lg border ${bgClass}`;
        estadoDiv.innerHTML = `
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 ${colorClass} rounded-full"></div>
            <span class="${textClass} font-medium">${esActivo ? 'ACTIVO' : 'INACTIVO'}</span>
        </div>
    `;
    }

    actualizarCondicionContribuyente(condicion) {
        const condicionDiv = document.getElementById('condicionContribuyente');
        if (!condicionDiv) return;

        const esHabido = condicion.toUpperCase() === 'HABIDO';
        const colorClass = esHabido ? 'bg-green-500' : 'bg-red-500';
        const bgClass = esHabido ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
        const textClass = esHabido ? 'text-green-800' : 'text-red-800';

        condicionDiv.className = `flex items-center px-4 py-3 rounded-lg border ${bgClass}`;
        condicionDiv.innerHTML = `
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 ${colorClass} rounded-full"></div>
            <span class="${textClass} font-medium">${esHabido ? 'HABIDO' : 'NO HABIDO'}</span>
        </div>
    `;
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

document.addEventListener('DOMContentLoaded', function () {
    const inputBusqueda = document.getElementById('busquedaCliente');
    const tbody = document.querySelector('table tbody');
    let timerBusqueda;

    inputBusqueda.addEventListener('input', function (e) {
        clearTimeout(timerBusqueda);
        const query = e.target.value.trim();

        // Si el input está vacío, recarga la página para mostrar la paginación normal
        if (query === '') {
            window.location.reload();
            return;
        }

        timerBusqueda = setTimeout(() => {
            fetch(`index.php?controller=superadmin&action=buscarClientesAjax&busqueda=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(clientes => actualizarTablaClientes(clientes));
        }, 300);
    });

    function actualizarTablaClientes(clientes) {
        if (!clientes.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="py-12 text-center">No hay clientes que coincidan</td></tr>`;
            return;
        }

        tbody.innerHTML = clientes.map((cliente, idx) => {
            // Buscar el establecimiento principal
            let establecimiento_principal = null;
            if (Array.isArray(cliente.establecimientos)) {
                establecimiento_principal = cliente.establecimientos.find(est => est.codigo_establecimiento === '0000');
            }

            return `
            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                <td class="py-4 px-6 text-center text-slate-600 font-medium">${idx + 1}</td>
                <td class="py-4 px-6">
                    <div class="flex items-center gap-3">
                        <div class="font-semibold text-slate-900">${cliente.razon_social}</div>
                    </div>
                </td>
                <td class="py-4 px-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                        ${cliente.ruc}
                    </span>
                </td>
                <td class="py-4 px-6">
                    <div class="flex items-center gap-1 text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="truncate" title="${cliente.distrito} - ${cliente.provincia}">
                            ${cliente.distrito} - ${cliente.provincia}
                        </span>
                    </div>
                </td>
                <td class="py-4 px-6 text-left align-middle">
                    ${cliente.estado == 1
                    ? `<button onclick="cambiarEstadoCliente(${cliente.id}, 2)"
                            class="inline-flex items-center min-w-[120px] justify-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-green-100 to-emerald-200 text-green-800 border border-green-300 hover:from-green-200 hover:to-emerald-300 transition-all duration-200 cursor-pointer"
                            title="Hacer clic para desactivar">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Activo
                        </button>`
                    : cliente.estado == 2
                        ? `<button onclick="cambiarEstadoCliente(${cliente.id}, 1)"
                            class="inline-flex items-center min-w-[120px] justify-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-100 to-pink-200 text-red-800 border border-red-300 hover:from-red-200 hover:to-pink-300 transition-all duration-200 cursor-pointer"
                            title="Hacer clic para activar">
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>Inactivo
                        </button>`
                        : `<span class="inline-flex items-center min-w-[120px] justify-center px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-500 border border-gray-300 cursor-not-allowed select-none" title="Cliente deshabilitado">
                            <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>Deshabilitado
                        </span>`
                }
                </td>
                <td class="py-4 px-6">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <a href="index.php?controller=superadmin&action=verCliente&id=${cliente.id}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                title="Ver detalles del cliente">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                        </div>
                        <div>
                            ${establecimiento_principal && establecimiento_principal.estado != 3
                    ? `<button type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                    title="Ingresar al establecimiento principal"
                                    onclick="entrarEstablecimientoSweet(${cliente.id}, ${establecimiento_principal.id})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                </button>`
                    : establecimiento_principal && establecimiento_principal.estado == 3
                        ? `<button type="button" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-400 text-white text-xs font-medium rounded-lg w-full justify-center cursor-not-allowed" title="Establecimiento deshabilitado" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                </button>`
                        : `<button type="button" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-400 text-white text-xs font-medium rounded-lg w-full justify-center cursor-not-allowed" title="No hay un establecimiento" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                </button>`
                }
                        </div>
                        <div>
                            <button onclick="abrirModalEdicion(${cliente.id})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                title="Editar cliente"
                                ${cliente.estado == 3 ? 'disabled class="opacity-50 cursor-not-allowed"' : ''}>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                        </div>
                        <div>
                            ${cliente.estado != 3
                    ? `<button type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                    title="Deshabilitar cliente"
                                    onclick="cambiarEstadoCliente(${cliente.id}, 3)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 6L6 18" />
                                        <circle cx="12" cy="12" r="9" />
                                    </svg>
                                </button>`
                    : `<button type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                    title="Habilitar cliente"
                                    onclick="cambiarEstadoCliente(${cliente.id}, 1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>`
                }
                        </div>
                    </div>
                </td>
            </tr>
            `;
        }).join('');
    }
});


// FUNCION VARIOS ESTABLECIMIENTOS

/*
<div class="flex gap-2">
                                            <!-- Botón Ver para el cliente -->
                                            <a href="index.php?controller=superadmin&action=verCliente&id=<?= $cliente['id'] ?>"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                                                title="Ver detalles del cliente">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Ver
                                            </a>

                                            <!-- Botón Editar -->
                                            <button onclick="abrirModalEdicion(<?= $cliente['id'] ?>)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                                                title="Editar cliente">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Editar
                                            </button>

                                            <!-- Flecha expandible para todos los clientes -->
                                            <button onclick="toggleEstablecimientos(<?= $cliente['id'] ?>); event.stopPropagation();"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-500 hover:bg-slate-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                                                title="Ver establecimientos">
                                                <svg id="arrow-<?= $cliente['id'] ?>" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        </div>
*/

/*
<!-- Filas de establecimientos (ocultas por defecto) - Para todos los clientes -->
                                <?php if (!empty($cliente['establecimientos'])): ?>
                                    <?php foreach ($cliente['establecimientos'] as $index => $establecimiento): ?>
                                        <?php if (!empty($establecimiento) && isset($establecimiento['id'])): ?>
                                            <tr id="establecimientos-<?= $cliente['id'] ?>-<?= $index ?>" class="hidden bg-slate-50 border-l-4 border-blue-300 establecimientos-<?= $cliente['id'] ?>">
                                                <td class="py-3 px-6 text-center">
                                                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                        </svg>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-6">
                                                    <div class="pl-4">
                                                        <div class="font-medium text-slate-700"><?= htmlspecialchars($establecimiento['etiqueta']) ?></div>
                                                        <div class="text-sm text-slate-500">Código: <?= htmlspecialchars($establecimiento['codigo_establecimiento']) ?></div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-6">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-200 text-slate-600">
                                                        <?= htmlspecialchars($establecimiento['tipo_establecimiento']) ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 px-6">
                                                    <div class="flex items-center gap-1 text-slate-500 text-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <span class="truncate" title="<?= htmlspecialchars($establecimiento['distrito'] . ' - ' . $establecimiento['provincia']) ?>">
                                                            <?= htmlspecialchars($establecimiento['distrito'] . ' - ' . $establecimiento['provincia']) ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-6">
                                                    <?php if ($establecimiento['estado'] == 1): ?>
                                                        <button onclick="cambiarEstadoEstablecimiento(<?= $establecimiento['id'] ?>, 2)"
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-green-100 to-emerald-200 text-green-800 border border-green-300 hover:from-green-200 hover:to-emerald-300 transition-all duration-200 cursor-pointer"
                                                            title="Hacer clic para desactivar">
                                                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Activo
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="cambiarEstadoEstablecimiento(<?= $establecimiento['id'] ?>, 1)"
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-100 to-pink-200 text-red-800 border border-red-300 hover:from-red-200 hover:to-pink-300 transition-all duration-200 cursor-pointer"
                                                            title="Hacer clic para activar">
                                                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>Inactivo
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 px-6">
                                                    <button type="button"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                                                        title="Acceso a este establecimiento"
                                                        onclick="entrarEstablecimientoSweet(<?= $cliente['id'] ?>, <?= $establecimiento['id'] ?>)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                        </svg>
                                                        Entrar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
*/