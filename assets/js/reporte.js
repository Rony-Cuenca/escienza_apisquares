document.addEventListener('DOMContentLoaded', function () {
    if (window.MESES_HABILITADOS && window.MESES_HABILITADOS.length > 0 && typeof flatpickr !== "undefined") {
        flatpickr("#mesPicker", {
            dateFormat: "Y-m",
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y"
                })
            ],
            enable: window.MESES_HABILITADOS,
            locale: "es"
        });
    }

    const btnExportPDF = document.querySelector('button[onclick*="exportarArchivo"][onclick*="PDF"]');
    const btnExportExcel = document.querySelector('button[onclick*="exportarArchivo"][onclick*="Excel"]');
    const mesInput = document.getElementById('mesPicker');

    function mostrarErrorSwal(mensaje) {
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: '¡Atención!',
                text: mensaje,
                confirmButtonColor: '#dc3545'
            });
        } else {
            alert(mensaje);
        }
    }

    function exportarArchivo(tipo) {
        const mes = mesInput.value;
        if (!mes) {
            mostrarErrorSwal('Por favor, seleccione un mes antes de exportar.');
            return;
        }
        if (tipo === 'Excel') {
            if (!window.ESTABLECIMIENTOS_EXPORT || window.ESTABLECIMIENTOS_EXPORT.length === 0) {
                mostrarErrorSwal('No hay establecimientos disponibles para exportar.');
                return;
            }
            const establecimientos = window.ESTABLECIMIENTOS_EXPORT;
            let html = `<style>
            .estab-modal-list { display: flex; flex-direction: column; gap: 10px; max-height: 320px; overflow-y: auto; }
            .estab-card { display: flex; align-items: center; background: #f8fafc; border-radius: 8px; padding: 10px 14px; box-shadow: 0 1px 4px #0001; transition: box-shadow 0.2s; border: 1px solid #e2e8f0; }
            .estab-card input[type=checkbox] { accent-color: #2563eb; width: 18px; height: 18px; margin-right: 12px; }
            .estab-card.disabled { opacity: 0.6; background: #f1f5f9; cursor: not-allowed; }
            .estab-card .estab-label { font-weight: 500; color: #222; }
            .estab-card .estab-sindatos { color: #888; font-size: 0.95em; margin-left: 8px; }
            .estab-todos { font-weight: 600; color: #2563eb; }
            .estab-global { font-weight: 700; color: #0d9488; background: #e0f2f1; border: 2px solid #0d9488; margin-bottom: 10px; }
            .estab-section-title { font-size: 1em; font-weight: 600; color: #2563eb; margin: 10px 0 2px 0; }
            .estab-section { margin-left: 10px; }
            </style>`;
            html += '<div class="estab-modal-list">';
            html += `<div class='estab-card estab-global'><input type='checkbox' id='chk_global_reporte'> <span class='estab-label'>Reporte Global</span></div>`;
            html += `<div class='estab-section-title'>Por establecimiento:</div>`;
            html += `<div class='estab-card estab-todos estab-section'><input type='checkbox' id='chk_todos_estab' checked> <span class='estab-label'><b>Todos</b></span></div>`;
            establecimientos.forEach(e => {
                const disabled = !e.tiene_datos ? "disabled" : "";
                html += `<div class='estab-card estab-section${!e.tiene_datos ? " disabled" : ""}'>`;
                html += `<input type='checkbox' class='chk_estab' value='${e.id}' ${e.tiene_datos ? "checked" : ""} ${disabled}>`;
                html += `<span class='estab-label'>${e.etiqueta}</span>`;
                if (!e.tiene_datos) html += `<span class='estab-sindatos'>(Sin datos)</span>`;
                html += `</div>`;
            });
            html += '</div>';
            Swal.fire({
                title: 'Selecciona establecimientos',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Exportar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const global = document.getElementById('chk_global_reporte').checked;
                    if (global) {
                        return { global: true };
                    }
                    const todos = document.getElementById('chk_todos_estab').checked;
                    let ids = [];
                    if (todos) {
                        ids = establecimientos.filter(e => e.tiene_datos).map(e => e.id);
                    } else {
                        document.querySelectorAll('.chk_estab:checked').forEach(cb => ids.push(cb.value));
                    }
                    if (ids.length === 0) {
                        Swal.showValidationMessage('Selecciona al menos un establecimiento');
                        return false;
                    }
                    return { global: false, ids };
                },
                didOpen: () => {
                    const global = document.getElementById('chk_global_reporte');
                    const todos = document.getElementById('chk_todos_estab');
                    const checks = document.querySelectorAll('.chk_estab');
                    // Si selecciona global, deshabilita todos los demás
                    global.addEventListener('change', function () {
                        if (global.checked) {
                            todos.checked = false;
                            // No deshabilites los demás, solo desactívalos
                            todos.disabled = false;
                            checks.forEach(cb => { if (!cb.parentElement.classList.contains('disabled')) cb.checked = false; });
                        }
                    });
                    // Si selecciona cualquier otro, deselecciona global
                    function desactivaGlobal() {
                        if (global.checked) {
                            global.checked = false;
                            global.disabled = false;
                            todos.disabled = false;
                            checks.forEach(cb => { if (!cb.parentElement.classList.contains('disabled')) cb.disabled = false; });
                        }
                    }
                    todos.addEventListener('change', function () {
                        if (todos.checked) {
                            desactivaGlobal();
                            checks.forEach(cb => { if (!cb.parentElement.classList.contains('disabled')) cb.checked = true; });
                        } else {
                            desactivaGlobal();
                            checks.forEach(cb => { if (!cb.parentElement.classList.contains('disabled')) cb.checked = false; });
                        }
                    });
                    checks.forEach(cb => {
                        cb.addEventListener('change', function () {
                            desactivaGlobal();
                            if (!cb.checked) {
                                todos.checked = false;
                            } else {
                                // Si todos los checkboxes (no deshabilitados) están seleccionados, activa 'Todos'
                                const activos = Array.from(checks).filter(c => !c.disabled);
                                if (activos.length > 0 && activos.every(c => c.checked)) {
                                    todos.checked = true;
                                }
                            }
                        });
                    });
                }
            }).then(result => {
                if (result.isConfirmed && result.value) {
                    if (result.value.global) {
                        let url = `index.php?controller=reporte&action=exportarExcel&mes=${encodeURIComponent(mes)}&global=1`;
                        window.location.href = url;
                    } else {
                        const ids = result.value.ids;
                        let url = `index.php?controller=reporte&action=exportarExcel&mes=${encodeURIComponent(mes)}`;
                        ids.forEach(id => {
                            url += `&id_establecimientos[]=${encodeURIComponent(id)}`;
                        });
                        window.location.href = url;
                    }
                }
            });
            return;
        }
        const estSelect = document.getElementById('establecimientoPicker');
        const idEstablecimiento = estSelect ? estSelect.value : '';
        let url = '';
        if (tipo === 'PDF') {
            if (!window.ESTABLECIMIENTOS_EXPORT || window.ESTABLECIMIENTOS_EXPORT.length === 0) {
                mostrarErrorSwal('No hay establecimientos disponibles para exportar.');
                return;
            }
            const establecimientos = window.ESTABLECIMIENTOS_EXPORT;
            let html = `<style>
            .estab-modal-list { display: flex; flex-direction: column; gap: 10px; max-height: 320px; overflow-y: auto; }
            .estab-card { display: flex; align-items: center; background: #f8fafc; border-radius: 8px; padding: 10px 14px; box-shadow: 0 1px 4px #0001; transition: box-shadow 0.2s; border: 1px solid #e2e8f0; }
            .estab-card input[type=checkbox] { accent-color: #2563eb; width: 18px; height: 18px; margin-right: 12px; }
            .estab-card.disabled { opacity: 0.6; background: #f1f5f9; cursor: not-allowed; }
            .estab-card .estab-label { font-weight: 500; color: #222; }
            .estab-card .estab-sindatos { color: #888; font-size: 0.95em; margin-left: 8px; }
            .estab-todos { font-weight: 600; color: #2563eb; }
            .estab-global { font-weight: 700; color: #0d9488; background: #e0f2f1; border: 2px solid #0d9488; margin-bottom: 10px; }
            .estab-section-title { font-size: 1em; font-weight: 600; color: #2563eb; margin: 10px 0 2px 0; }
            .estab-section { margin-left: 10px; }
            </style>`;
            html += '<div class="estab-modal-list">';
            html += `<div class='estab-card estab-global'><input type='checkbox' id='chk_global_reporte'> <span class='estab-label'>Reporte Global</span></div>`;
            html += `<div class='estab-section-title'>Por establecimiento:</div>`;
            html += `<div class='estab-card estab-todos estab-section'><input type='checkbox' id='chk_todos_estab_pdf' checked> <span class='estab-label'><b>Todos</b></span></div>`;
            establecimientos.forEach(e => {
                const disabled = !e.tiene_datos ? "disabled" : "";
                html += `<div class='estab-card estab-section${!e.tiene_datos ? " disabled" : ""}'>`;
                html += `<input type='checkbox' class='chk_estab_pdf' value='${e.id}' ${e.tiene_datos ? "checked" : ""} ${disabled}>`;
                html += `<span class='estab-label'>${e.etiqueta}</span>`;
                if (!e.tiene_datos) html += `<span class='estab-sindatos'>(Sin datos)</span>`;
                html += `</div>`;
            });
            html += '</div>';
            Swal.fire({
                title: 'REPORTES',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Exportar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const global = document.getElementById('chk_global_reporte').checked;
                    if (global) {
                        return { global: true };
                    }
                    const todos = document.getElementById('chk_todos_estab_pdf').checked;
                    let ids = [];
                    if (todos) {
                        ids = establecimientos.filter(e => e.tiene_datos).map(e => e.id);
                    } else {
                        document.querySelectorAll('.chk_estab_pdf:checked').forEach(cb => ids.push(cb.value));
                    }
                    if (ids.length === 0) {
                        Swal.showValidationMessage('Selecciona al menos un establecimiento');
                        return false;
                    }
                    return { global: false, ids };
                },
                didOpen: () => {
                    const global = document.getElementById('chk_global_reporte');
                    const todos = document.getElementById('chk_todos_estab_pdf');
                    const checks = document.querySelectorAll('.chk_estab_pdf');
                    global.addEventListener('change', function () {
                        if (global.checked) {
                            todos.checked = false;
                            todos.disabled = false;
                            checks.forEach(cb => { if (!cb.parentElement.classList.contains('disabled')) cb.checked = false; });
                        }
                    });
                    todos.addEventListener('change', function () {
                        if (todos.checked) {
                            if (global.checked) global.checked = false;
                            checks.forEach(cb => { if (!cb.parentElement.classList.contains('disabled')) cb.checked = true; });
                        } else {
                            if (global.checked) global.checked = false;
                            checks.forEach(cb => { if (!cb.parentElement.classList.contains('disabled')) cb.checked = false; });
                        }
                    });
                    checks.forEach(cb => {
                        cb.addEventListener('change', function () {
                            if (global.checked) global.checked = false;
                            if (!cb.checked) {
                                todos.checked = false;
                            } else {
                                const activos = Array.from(checks).filter(c => !c.disabled);
                                if (activos.length > 0 && activos.every(c => c.checked)) {
                                    todos.checked = true;
                                }
                            }
                        });
                    });
                }
            }).then(result => {
                if (result.isConfirmed && result.value) {
                    if (result.value.global) {
                        let url = `index.php?controller=reporte&action=exportarPDF&mes=${encodeURIComponent(mes)}&global=1`;
                        window.location.href = url;
                    } else {
                        const ids = result.value.ids;
                        let url = `index.php?controller=reporte&action=exportarPDF&mes=${encodeURIComponent(mes)}`;
                        ids.forEach(id => {
                            url += `&id_establecimientos[]=${encodeURIComponent(id)}`;
                        });
                        window.location.href = url;
                    }
                }
            });
            return;
        }
    }

    if (btnExportPDF) {
        btnExportPDF.addEventListener('click', function (e) {
            if (btnExportPDF.disabled) {
                e.preventDefault();
                mostrarErrorSwal('No hay datos para exportar en esta sucursal.');
                return;
            }
            e.preventDefault();
            exportarArchivo('PDF');
        });
    }
    if (btnExportExcel) {
        btnExportExcel.addEventListener('click', function (e) {
            if (btnExportExcel.disabled) {
                e.preventDefault();
                mostrarErrorSwal('No hay datos para exportar en esta sucursal.');
                return;
            }
            e.preventDefault();
            exportarArchivo('Excel');
        });
    }
});