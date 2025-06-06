document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalUsuario');
    const btnNuevoUsuario = document.getElementById('btnNuevoUsuario');
    const btnCerrarModal = document.getElementById('btnCerrarModal');
    const btnCancelarModal = document.getElementById('btnCancelarModal');
    const formUsuario = document.getElementById('formUsuario');
    const modalTitulo = document.getElementById('modalTitulo');
    const modalIdUsuario = document.getElementById('modalIdUsuario');
    const modalUsuarioNombre = document.getElementById('modalUsuarioNombre');
    const modalUsuarioCorreo = document.getElementById('modalUsuarioCorreo');
    const modalUsuarioRol = document.getElementById('modalUsuarioRol');
    const modalUsuarioSucursal = document.getElementById('modalUsuarioSucursal');
    const modalUsuarioContraseña = document.getElementById('modalUsuarioContraseña');
    const modalUsuarioConfirmarContraseña = document.getElementById('modalUsuarioConfirmarContraseña');
    const modalUsuarioEstado = document.getElementById('modalUsuarioEstado');
    const correoCliente = btnNuevoUsuario.dataset.correoCliente;

    const cerrarMenus = () => {
        document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
            const menu = btn.nextElementSibling;
            if (menu && menu.classList.contains('hidden') === false) {
                menu.classList.add('hidden');
            }
        });
    };

    document.querySelectorAll('[data-action="toggleMenu"]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation(); 
            cerrarMenus();
            const menu = btn.nextElementSibling;
            if (menu) {
                menu.classList.toggle('hidden');
            }
        });
    });

    document.addEventListener('click', () => {
        cerrarMenus();
    });

    btnNuevoUsuario.addEventListener('click', () => {
        cerrarMenus();
        modalTitulo.textContent = 'Nuevo Usuario';
        formUsuario.action = 'index.php?controller=usuario&action=crear';
        modalIdUsuario.value = '';
        modalUsuarioNombre.value = '';
        modalUsuarioCorreo.value = correoCliente || 'Correo no disponible';
        modalUsuarioCorreo.readOnly = true;
        modalUsuarioRol.value = '';
        modalUsuarioSucursal.value = '';
        modalUsuarioContraseña.value = '';
        modalUsuarioConfirmarContraseña.value = '';
        modalUsuarioEstado.value = '1';
        modal.classList.remove('hidden');
    });

    const cerrarModal = () => {
        modal.classList.add('hidden');
    };
    btnCerrarModal.addEventListener('click', cerrarModal);
    btnCancelarModal.addEventListener('click', cerrarModal);

    document.querySelectorAll('[data-action="editar"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            cerrarMenus();
            modalTitulo.textContent = 'Editar Usuario';
            formUsuario.action = 'index.php?controller=usuario&action=editar';
            modalIdUsuario.value = btn.dataset.id;
            modalUsuarioNombre.value = btn.dataset.usuario || '';
            modalUsuarioCorreo.value = btn.dataset.correo || 'Correo no disponible';
            modalUsuarioCorreo.readOnly = true;
            modalUsuarioRol.value = btn.dataset.rol || '';
            modalUsuarioSucursal.value = btn.dataset.sucursal || '';
            modalUsuarioEstado.value = btn.dataset.estado || '1';
            modalUsuarioContraseña.value = '';
            modalUsuarioConfirmarContraseña.value = '';
            modal.classList.remove('hidden');
        });
    });
});