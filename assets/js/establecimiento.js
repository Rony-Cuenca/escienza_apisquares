document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalEstablecimiento');
    const modalTitulo = document.getElementById('modalEstTitulo');
    const btnNuevoEstablecimiento = document.getElementById('btnNuevoEstablecimiento');
    const btnCerrarModal = document.getElementById('btnCerrarModalEst');
    const btnCancelarModal = document.getElementById('btnCancelarModalEst');
    const formEstablecimiento = document.getElementById('formEstablecimiento');

    const modalInputs = {
        id: document.getElementById('modalEstId'),
        ruc: document.getElementById('modalEstRuc'),
        razon_social: document.getElementById('modalEstRazon'),
        direccion: document.getElementById('modalEstDireccion'),
        estado: document.getElementById('modalEstEstado')
    };

    let errorRuc = document.getElementById('errorRuc');
    if (!errorRuc) {
        errorRuc = document.createElement('div');
        errorRuc.id = 'errorRuc';
        errorRuc.className = 'text-red-600 text-sm mb-1 hidden';
        modalInputs.ruc.parentNode.appendChild(errorRuc);
    }

    function mostrarErrorRuc(mensaje) {
        errorRuc.textContent = mensaje;
        errorRuc.classList.remove('hidden');
    }
    function ocultarErrorRuc() {
        errorRuc.textContent = '';
        errorRuc.classList.add('hidden');
    }

    btnNuevoEstablecimiento.addEventListener('click', () => {
        abrirModalEstablecimiento({
            titulo: 'Nuevo Establecimiento',
            action: 'index.php?controller=establecimiento&action=crear'
        });
    });

    document.querySelectorAll('[data-action="editar"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            abrirModalEstablecimiento({
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

    function cerrarMenus() {
        document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
            const menu = btn.nextElementSibling;
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    }
    document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            cerrarMenus();
            const menu = btn.nextElementSibling;
            if (menu) menu.classList.toggle('hidden');
        });
    });
    document.addEventListener('click', cerrarMenus);

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

    function abrirModalEstablecimiento({ titulo, action, values = {} }) {
        modalTitulo.textContent = titulo;
        formEstablecimiento.action = action;
        modalInputs.id.value = values.id || '';
        modalInputs.ruc.value = values.ruc || '';
        modalInputs.razon_social.value = values.razon_social || '';
        modalInputs.direccion.value = values.direccion || '';
        modalInputs.estado.value = values.estado || '1';
        ocultarErrorRuc();
        modal.classList.remove('hidden');
        setTimeout(() => modalInputs.ruc.focus(), 100);
    }
    function cerrarModalEstablecimiento() {
        modal.classList.add('hidden');
    }
    btnCerrarModal.addEventListener('click', cerrarModalEstablecimiento);
    btnCancelarModal.addEventListener('click', cerrarModalEstablecimiento);

    formEstablecimiento.addEventListener('submit', function (e) {
        e.preventDefault();
        ocultarErrorRuc();

        const ruc = modalInputs.ruc.value.trim();
        const razon = modalInputs.razon_social.value.trim();
        const direccion = modalInputs.direccion.value.trim();
        const id = modalInputs.id.value || 0;

        if (!ruc.match(/^\d{11}$/)) {
            mostrarErrorRuc('El RUC debe tener 11 dígitos numéricos.');
            modalInputs.ruc.focus();
            return false;
        }
        if (!razon) {
            modalInputs.razon_social.focus();
            return false;
        }
        if (!direccion) {
            modalInputs.direccion.focus();
            return false;
        }

        fetch(`index.php?controller=establecimiento&action=verificarRuc&ruc=${encodeURIComponent(ruc)}&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.existe) {
                    mostrarErrorRuc('El RUC ya existe.');
                    modalInputs.ruc.focus();
                    return false;
                } else {
                    formEstablecimiento.submit();
                }
            })
            .catch(() => {
                mostrarErrorRuc('No se pudo verificar el RUC');
            });
    });
});