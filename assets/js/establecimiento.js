class EstablecimientoController {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        document.getElementById('btnSincronizar')?.addEventListener('click', () => {
            this.sincronizarEstablecimientos();
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
                const idEstablecimiento = btn.dataset.id;
                const estadoEstablecimiento = btn.dataset.estado;

                if (!idEstablecimiento || !estadoEstablecimiento) {
                    Swal.fire('Error', 'Datos inválidos para cambiar el estado.', 'error');
                    return;
                }

                this.cambiarEstado(idEstablecimiento, estadoEstablecimiento);
            });
        });

        document.querySelectorAll('[data-action="editarEstablecimiento"]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const idEstablecimiento = btn.dataset.id;

                if (!idEstablecimiento) {
                    Swal.fire('Error', 'ID de establecimiento inválido.', 'error');
                    return;
                }

                this.abrirModalEdicion(idEstablecimiento);
            });
        });

        document.getElementById('btnNuevoEstablecimiento')?.addEventListener('click', () => {
            this.abrirModalNuevo();
        });

        this.bindModalEvents();
    }

    bindModalEvents() {
        document.getElementById('btnCancelarEdicion')?.addEventListener('click', () => {
            this.cerrarModalEdicion();
        });

        document.getElementById('modalEditarEstablecimiento')?.addEventListener('click', (e) => {
            if (e.target.id === 'modalEditarEstablecimiento') {
                this.cerrarModalEdicion();
            }
        });

        document.getElementById('formEditarEstablecimiento')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.guardarCambios();
        });

        document.getElementById('btnCancelarNuevo')?.addEventListener('click', () => {
            this.cerrarModalNuevo();
        });

        document.getElementById('modalNuevoEstablecimiento')?.addEventListener('click', (e) => {
            if (e.target.id === 'modalNuevoEstablecimiento') {
                this.cerrarModalNuevo();
            }
        });

        document.getElementById('formNuevoEstablecimiento')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.crearEstablecimiento();
        });

        document.getElementById('nuevoCodigo')?.addEventListener('blur', (e) => {
            this.verificarCodigoEstablecimiento(e.target.value);
        });
    }

    sincronizarEstablecimientos() {
        Swal.fire({
            title: '¿Sincronizar establecimientos?',
            text: 'Se consultará SUNAT para obtener los establecimientos actualizados.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, sincronizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sincronizando...',
                    text: 'Consultando datos de SUNAT',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('index.php?controller=establecimiento&action=sincronizarEstablecimientos', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Sincronización completada!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#16a34a'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.error || 'No se pudo sincronizar los establecimientos.',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Error de conexión al sincronizar.',
                            icon: 'error'
                        });
                    });
            }
        });
    }

    cambiarEstado(id, estado) {
        // Determinar la acción basada en el estado objetivo
        let textoAccion, textoEstado;
        
        if (estado === '1') {
            // Cambiar a activo
            textoAccion = 'activar';
            textoEstado = 'activó';
        } else if (estado === '2') {
            // Cambiar a inactivo
            textoAccion = 'desactivar';
            textoEstado = 'desactivó';
        } else if (estado === '3') {
            // Cambiar a deshabilitado
            textoAccion = 'deshabilitar';
            textoEstado = 'deshabilitó';
        } else {
            textoAccion = 'cambiar el estado de';
            textoEstado = 'cambió el estado de';
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas ${textoAccion} este establecimiento?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0018F4',
            cancelButtonColor: '#d33',
            confirmButtonText: `Sí, ${textoAccion}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`index.php?controller=establecimiento&action=cambiarEstado&id=${id}&estado=${estado}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                '¡Listo!',
                                `El establecimiento se ${textoEstado} correctamente.`,
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
    }

    abrirModalEdicion(idEstablecimiento) {
        // Obtener datos del establecimiento
        fetch(`index.php?controller=establecimiento&action=editarEstablecimiento&id=${idEstablecimiento}`)
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editEtiqueta').value = data.etiqueta || '';
                    document.getElementById('editDireccion').value = data.direccion || '';
                    document.getElementById('modalEditarEstablecimiento').classList.remove('hidden');
                } else {
                    Swal.fire('Error', 'No se pudieron cargar los datos del establecimiento.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al cargar los datos del establecimiento.', 'error');
            });
    }

    abrirModalNuevo() {
        document.getElementById('modalNuevoEstablecimiento').classList.remove('hidden');
    }

    cerrarModalEdicion() {
        document.getElementById('modalEditarEstablecimiento').classList.add('hidden');
        document.getElementById('formEditarEstablecimiento').reset();
    }

    guardarCambios() {
        const formData = new FormData(document.getElementById('formEditarEstablecimiento'));

        const etiqueta = formData.get('etiqueta');
        const direccion = formData.get('direccion');

        if (!etiqueta || etiqueta.trim() === '') {
            Swal.fire('Error', 'La etiqueta es obligatoria.', 'error');
            return;
        }

        if (!direccion || direccion.trim() === '') {
            Swal.fire('Error', 'La dirección es obligatoria.', 'error');
            return;
        }

        Swal.fire({
            title: 'Guardando cambios...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('index.php?controller=establecimiento&action=guardarEdicion', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Éxito',
                        text: data.message || 'Establecimiento actualizado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        this.cerrarModalEdicion();
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.error || 'No se pudo actualizar el establecimiento.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al actualizar el establecimiento.', 'error');
            });
    }

    cerrarModalNuevo() {
        document.getElementById('modalNuevoEstablecimiento').classList.add('hidden');
        document.getElementById('formNuevoEstablecimiento').reset();
        this.limpiarErrores();
    }

    verificarCodigoEstablecimiento(codigo) {
        if (!codigo || codigo.trim() === '') return;

        fetch(`index.php?controller=establecimiento&action=verificarCodigoEstablecimiento&codigo=${encodeURIComponent(codigo)}`)
            .then(response => response.json())
            .then(data => {
                const input = document.getElementById('nuevoCodigo');
                if (data.existe) {
                    input.classList.add('border-red-500');
                    input.classList.remove('border-gray-300');
                    this.mostrarError(input, 'Este código ya existe para otro establecimiento');
                } else {
                    input.classList.remove('border-red-500');
                    input.classList.add('border-gray-300');
                    this.limpiarError(input);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    crearEstablecimiento() {
        const formData = new FormData(document.getElementById('formNuevoEstablecimiento'));
        const campos = {
            codigo_establecimiento: 'Código de establecimiento',
            tipo_establecimiento: 'Tipo de establecimiento',
            etiqueta: 'Etiqueta',
            direccion: 'Dirección'
        };

        for (const [campo, nombre] of Object.entries(campos)) {
            const valor = formData.get(campo);
            if (!valor || valor.trim() === '') {
                Swal.fire('Error', `${nombre} es obligatorio.`, 'error');
                return;
            }
        }

        Swal.fire({
            title: 'Creando establecimiento...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('index.php?controller=establecimiento&action=crearEstablecimiento', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Éxito',
                        text: data.message || 'Establecimiento creado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        this.cerrarModalNuevo();
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.error || 'No se pudo crear el establecimiento.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al crear el establecimiento.', 'error');
            });
    }

    mostrarError(input, mensaje) {
        this.limpiarError(input);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
        errorDiv.textContent = mensaje;
        input.parentNode.appendChild(errorDiv);
    }

    limpiarError(input) {
        const errorMsg = input.parentNode.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }

    limpiarErrores() {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
    }

    cerrarMenus() {
        document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
            const menu = btn.nextElementSibling;
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new EstablecimientoController();
});
