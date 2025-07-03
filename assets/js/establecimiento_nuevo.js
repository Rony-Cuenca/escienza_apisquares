class EstablecimientoController {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Botón de sincronización
        document.getElementById('btnSincronizar')?.addEventListener('click', () => {
            this.sincronizarEstablecimientos();
        });

        // Menús de acciones
        document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.cerrarMenus();
                const menu = btn.nextElementSibling;
                if (menu) menu.classList.toggle('hidden');
            });
        });

        // Cerrar menús al hacer click fuera
        document.addEventListener('click', () => this.cerrarMenus());

        // Cambiar estado de establecimientos
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
                // Mostrar loading
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
                            location.reload(); // Recargar para mostrar los nuevos datos
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
        const textoAccion = estado === '3' ? 'deshabilitar' : 'habilitar';
        
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
                                `El establecimiento se ${textoAccion === 'deshabilitar' ? 'deshabilitó' : 'habilitó'} correctamente.`, 
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

    cerrarMenus() {
        document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
            const menu = btn.nextElementSibling;
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    }
}

// Inicializar cuando se cargue el DOM
document.addEventListener('DOMContentLoaded', () => {
    new EstablecimientoController();
});
