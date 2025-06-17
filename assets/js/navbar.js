document.addEventListener('DOMContentLoaded', function () {
    const btnAdminMenu = document.getElementById('btnAdminMenu');
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
});