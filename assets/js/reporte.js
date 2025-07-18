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
            // Modal SweetAlert2 para seleccionar establecimientos
            if (!window.ESTABLECIMIENTOS_EXPORT || window.ESTABLECIMIENTOS_EXPORT.length === 0) {
                mostrarErrorSwal('No hay establecimientos disponibles para exportar.');
                return;
            }
            const establecimientos = window.ESTABLECIMIENTOS_EXPORT;
            let html = '<div style="text-align:left;max-height:300px;overflow-y:auto">';
            html += `<label><input type='checkbox' id='chk_todos_estab' checked style='margin-right:6px'> <b>Todos</b></label><br>`;
            establecimientos.forEach(e => {
                html += `<label><input type='checkbox' class='chk_estab' value='${e.id}' checked style='margin-right:6px'>${e.etiqueta}</label><br>`;
            });
            html += '</div>';
            Swal.fire({
                title: 'Selecciona establecimientos',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Exportar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const todos = document.getElementById('chk_todos_estab').checked;
                    let ids = [];
                    if (todos) {
                        ids = establecimientos.map(e => e.id);
                    } else {
                        document.querySelectorAll('.chk_estab:checked').forEach(cb => ids.push(cb.value));
                    }
                    if (ids.length === 0) {
                        Swal.showValidationMessage('Selecciona al menos un establecimiento');
                        return false;
                    }
                    return ids;
                },
                didOpen: () => {
                    const todos = document.getElementById('chk_todos_estab');
                    const checks = document.querySelectorAll('.chk_estab');
                    todos.addEventListener('change', function() {
                        checks.forEach(cb => cb.checked = todos.checked);
                    });
                    checks.forEach(cb => {
                        cb.addEventListener('change', function() {
                            if (!cb.checked) todos.checked = false;
                            else if ([...checks].every(c => c.checked)) todos.checked = true;
                        });
                    });
                }
            }).then(result => {
                if (result.isConfirmed && result.value) {
                    const ids = result.value;
                    let url = `index.php?controller=reporte&action=exportarExcel&mes=${encodeURIComponent(mes)}`;
                    ids.forEach(id => {
                        url += `&id_establecimientos[]=${encodeURIComponent(id)}`;
                    });
                    window.location.href = url;
                }
            });
            return;
        }
        // PDF: comportamiento anterior
        const estSelect = document.getElementById('establecimientoPicker');
        const idEstablecimiento = estSelect ? estSelect.value : '';
        let url = '';
        if (tipo === 'PDF') {
            url = `index.php?controller=reporte&action=exportarPDF&mes=${encodeURIComponent(mes)}${idEstablecimiento ? `&id_establecimiento=${encodeURIComponent(idEstablecimiento)}` : ''}`;
            window.location.href = url;
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