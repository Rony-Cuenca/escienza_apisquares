document.addEventListener('DOMContentLoaded', function () {
    const btnAdminMenu = document.getElementById('btnAdminMenu');
    const btnSubMenu = document.getElementById('btnUsuariosEstablecimientos');
    const submenu = document.getElementById('submenuUsuariosEstablecimientos');
    const adminDropdown = document.getElementById('adminDropdown');

    if (btnAdminMenu && adminDropdown) {
        btnAdminMenu.addEventListener('click', function (e) {
            e.stopPropagation();
            adminDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function () {
            adminDropdown.classList.remove('show');
        });
    }

    if (btnSubMenu && submenu) {
        btnSubMenu.addEventListener('click', function (e) {
            e.stopPropagation();
            submenu.classList.toggle('hidden');
        });
        document.addEventListener('click', function () {
            submenu.classList.add('hidden');
        });
        submenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
});