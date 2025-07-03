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
        this.successDiv = document.getElementById('successRuc') || this._crearSuccessDiv(this.inputs.ruc);
        this.btnValidarRuc = document.getElementById('btnValidarRuc');
        this.loadingRuc = document.getElementById('loadingRuc');
        this.establecimientosContainer = document.getElementById('establecimientosContainer');
        this.selectEstablecimiento = document.getElementById('selectEstablecimiento');
        this.rucValidado = false;
        this.establecimientosData = [];
        this.infoBasicaTemp = null;

        this._bindEvents(config);
        this._bindRucValidation();
    }

    _crearErrorDiv(input, id) {
        const div = document.createElement('div');
        div.id = id;
        div.className = 'text-red-600 text-sm mb-1 hidden';
        input.parentNode.appendChild(div);
        return div;
    }

    _crearSuccessDiv(input) {
        const div = document.createElement('div');
        div.id = 'successRuc';
        div.className = 'text-green-600 text-sm mb-1 hidden';
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
        this.errorDiv.classList.add('flex');
        this.ocultarSuccess();
    }

    mostrarSuccess(mensaje) {
        this.inputs.ruc.classList.remove('border-red-600', 'bg-red-50');
        this.inputs.ruc.classList.add('border-green-600', 'bg-green-50');
        this.successDiv.innerHTML = `
        <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>${mensaje}</span>
    `;
        this.successDiv.classList.remove('hidden');
        this.successDiv.classList.add('flex');
        this.ocultarError();
    }

    ocultarError() {
        this.inputs.ruc.classList.remove('border-red-600', 'bg-red-50');
        this.errorDiv.textContent = '';
        this.errorDiv.classList.add('hidden');
        this.errorDiv.classList.remove('flex');
    }

    ocultarSuccess() {
        this.inputs.ruc.classList.remove('border-green-600', 'bg-green-50');
        this.successDiv.textContent = '';
        this.successDiv.classList.add('hidden');
        this.successDiv.classList.remove('flex');
    }

    mostrarLoading(mostrar = true) {
        if (mostrar) {
            this.btnValidarRuc.classList.add('hidden');
            this.loadingRuc.classList.remove('hidden');
            this.loadingRuc.classList.add('flex');
        } else {
            this.btnValidarRuc.classList.remove('hidden');
            this.loadingRuc.classList.add('hidden');
            this.loadingRuc.classList.remove('flex');
        }
    }

    abrirModal({ titulo, action, values = {} }) {
        this.titulo.textContent = titulo;
        this.form.action = action;
        this.inputs.id.value = values.id || '';
        this.inputs.ruc.value = values.ruc || '';
        this.inputs.razon_social.value = values.razon_social || '';
        this.inputs.direccion.value = values.direccion || '';
        this.inputs.estado.value = values.estado || '1';
        this.rucValidado = !!values.ruc; // Si tiene RUC (edición), considerarlo validado
        this.ocultarError();
        this.ocultarSuccess();
        this.ocultarEstablecimientos();
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
            const establecimientoSelect = document.getElementById('modalEstDireccion');
            const id = this.inputs.id.value || 0;

            if (!ruc.match(/^\d{11}$/)) {
                this.mostrarError('El RUC debe tener 11 dígitos numéricos.');
                this.inputs.ruc.focus();
                return false;
            }

            if (!id && !this.rucValidado) {
                this.mostrarError('Debe validar el RUC con SUNAT antes de continuar.');
                this.inputs.ruc.focus();
                return false;
            }

            if (!razon) {
                this.inputs.razon_social.focus();
                Swal.fire('Error', 'La razón social es obligatoria.', 'error');
                return false;
            }

            if (!establecimientoSelect.value) {
                establecimientoSelect.focus();
                Swal.fire('Error', 'Debe seleccionar un establecimiento válido.', 'error');
                return false;
            }

            // Verificar que el JSON del establecimiento sea válido
            try {
                const establecimientoData = JSON.parse(establecimientoSelect.value);
                if (!establecimientoData.direccion && !establecimientoData.direccion_completa) {
                    Swal.fire('Error', 'El establecimiento seleccionado no tiene dirección válida.', 'error');
                    return false;
                }
            } catch (e) {
                Swal.fire('Error', 'Error en los datos del establecimiento seleccionado.', 'error');
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

    _bindRucValidation() {
        this.inputs.ruc.addEventListener('input', (e) => {
            const ruc = e.target.value.replace(/\D/g, '');
            e.target.value = ruc;
            
            this.rucValidado = false;
            this.ocultarError();
            this.ocultarSuccess();
            this.ocultarEstablecimientos();
            
            if (ruc.length === 11) {
                this.validarRucConAPI();
            } else if (ruc.length > 0) {
                this.btnValidarRuc.disabled = ruc.length !== 11;
            }
        });

        this.btnValidarRuc.addEventListener('click', () => {
            this.validarRucConAPI();
        });

        this.selectEstablecimiento.addEventListener('change', (e) => {
            const value = e.target.value;
            
            if (value !== '' && this.establecimientosData[value]) {
                // Usar datos del establecimiento seleccionado
                const establecimiento = this.establecimientosData[value];
                this.aplicarDatosEstablecimiento(establecimiento);
            }
        });

        // Prevenir caracteres no numéricos
        this.inputs.ruc.addEventListener('keypress', (e) => {
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }

    async validarRucConAPI() {
        const ruc = this.inputs.ruc.value.trim();
        
        if (!ruc.match(/^\d{11}$/)) {
            this.mostrarError('El RUC debe tener 11 dígitos numéricos.');
            return;
        }

        this.mostrarLoading(true);
        this.ocultarError();
        this.ocultarSuccess();

        try {
            // Primero validar RUC y obtener información básica
            const id = this.inputs.id.value || 0;
            const response = await fetch(`index.php?controller=establecimiento&action=validarRucSunat&ruc=${encodeURIComponent(ruc)}&id=${id}`);
            const data = await response.json();

            if (data.success) {
                this.rucValidado = true;
                
                // Completar razón social automáticamente
                if (data.tipo === 'con_establecimientos' && data.info_basica?.nombre_o_razon_social) {
                    this.inputs.razon_social.value = data.info_basica.nombre_o_razon_social;
                } else if (data.tipo === 'info_basica' && data.data?.nombre_o_razon_social) {
                    this.inputs.razon_social.value = data.data.nombre_o_razon_social;
                }

                // Cargar establecimientos desde la nueva API
                await this.cargarEstablecimientosDesdeAPI(ruc);

                // Verificar si ya existe en BD
                if (data.existe_en_bd) {
                    this.mostrarError('El RUC ya existe en el sistema.');
                } else {
                    this.mostrarSuccess(data.message || 'RUC validado correctamente');
                }

            } else {
                this.rucValidado = false;
                this.limpiarSelectEstablecimientos();
                this.mostrarError(data.error || 'No se pudo validar el RUC');
            }
        } catch (error) {
            this.rucValidado = false;
            this.mostrarError('Error de conexión al validar RUC');
            console.error('Error:', error);
        } finally {
            this.mostrarLoading(false);
        }
    }

    async cargarEstablecimientosDesdeAPI(ruc) {
        try {
            const response = await fetch(`index.php?controller=establecimiento&action=obtenerEstablecimientosPorRuc&ruc=${encodeURIComponent(ruc)}`);
            const data = await response.json();

            if (data.success && data.establecimientos) {
                this.cargarOpcionesEstablecimientos(data.establecimientos);
            } else {
                this.limpiarSelectEstablecimientos();
                console.warn('No se pudieron cargar establecimientos:', data.error || 'Sin establecimientos');
            }
        } catch (error) {
            this.limpiarSelectEstablecimientos();
            console.error('Error al cargar establecimientos:', error);
        }
    }

    cargarOpcionesEstablecimientos(establecimientos) {
        const select = document.getElementById('modalEstDireccion');
        select.innerHTML = '<option value="">Seleccione un establecimiento...</option>';
        
        establecimientos.forEach((est) => {
            const option = document.createElement('option');
            option.value = JSON.stringify(est); // Guardamos todos los datos del establecimiento
            option.textContent = est.descripcion;
            select.appendChild(option);
        });
    }

    aplicarDatosEstablecimientoSeleccionado(establecimiento) {
        // Ya no necesitamos campos ocultos, los datos se envían directamente en el select
        console.log('Establecimiento seleccionado:', establecimiento);
    }

    limpiarSelectEstablecimientos() {
        const select = document.getElementById('modalEstDireccion');
        select.innerHTML = '<option value="">Primero ingrese un RUC válido</option>';
    }



    async validarRucConAPI() {
        const ruc = this.inputs.ruc.value.trim();
        
        if (!ruc.match(/^\d{11}$/)) {
            this.mostrarError('El RUC debe tener 11 dígitos numéricos.');
            return;
        }

        this.mostrarLoading(true);
        this.ocultarError();
        this.ocultarSuccess();

        try {
            const id = this.inputs.id.value || 0;
            const response = await fetch(`index.php?controller=establecimiento&action=validarRucSunat&ruc=${encodeURIComponent(ruc)}&id=${id}`);
            const data = await response.json();

            if (data.success) {
                this.rucValidado = true;
                
                // Completar razón social automáticamente
                if (data.tipo === 'con_establecimientos' && data.info_basica?.nombre_o_razon_social) {
                    this.inputs.razon_social.value = data.info_basica.nombre_o_razon_social;
                } else if (data.tipo === 'info_basica' && data.data?.nombre_o_razon_social) {
                    this.inputs.razon_social.value = data.data.nombre_o_razon_social;
                }

                // Cargar establecimientos desde la nueva API
                await this.cargarEstablecimientosDesdeAPI(ruc);

                // Verificar si ya existe en BD
                if (data.existe_en_bd) {
                    this.mostrarError('El RUC ya existe en el sistema.');
                } else {
                    this.mostrarSuccess(data.message || 'RUC validado correctamente');
                }

            } else {
                this.rucValidado = false;
                this.limpiarSelectEstablecimientos();
                this.mostrarError(data.error || 'No se pudo validar el RUC');
            }
        } catch (error) {
            this.rucValidado = false;
            this.mostrarError('Error de conexión al validar RUC');
            console.error('Error:', error);
        } finally {
            this.mostrarLoading(false);
        }
    }

    mostrarEstablecimientos(establecimientos, infoBasica = null) {
        this.establecimientosData = establecimientos;
        this.selectEstablecimiento.innerHTML = '<option value="">Seleccione un establecimiento...</option>';
        
        establecimientos.forEach((establecimiento, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = establecimiento.descripcion || 'Sin descripción';
            this.selectEstablecimiento.appendChild(option);
        });
        
        this.establecimientosContainer.classList.remove('hidden');
    }

    aplicarDatosEstablecimiento(establecimiento) {
        // Log para debugging
        console.log('Establecimiento seleccionado:', {
            codigo: establecimiento.codigo,
            tipo: establecimiento.tipo,
            direccion: establecimiento.direccion || establecimiento.direccion_completa,
            distrito: establecimiento.distrito,
            provincia: establecimiento.provincia,
            departamento: establecimiento.departamento
        });
    }

    ocultarEstablecimientos() {
        this.establecimientosContainer.classList.add('hidden');
        this.selectEstablecimiento.innerHTML = '<option value="">Seleccione un establecimiento...</option>';
        this.establecimientosData = [];
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
