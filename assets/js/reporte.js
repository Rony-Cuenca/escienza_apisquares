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
        const estSelect = document.getElementById('establecimientoPicker');
        const idEstablecimiento = estSelect ? estSelect.value : '';
        if (!mes) {
            mostrarErrorSwal('Por favor, seleccione un mes antes de exportar.');
            return;
        }
        let url = '';
        if (tipo === 'PDF') {
            url = `index.php?controller=reporte&action=exportarPDF&mes=${encodeURIComponent(mes)}${idEstablecimiento ? `&id_establecimiento=${encodeURIComponent(idEstablecimiento)}` : ''}`;
        } else if (tipo === 'Excel') {
            url = `index.php?controller=reporte&action=exportarExcel&mes=${encodeURIComponent(mes)}${idEstablecimiento ? `&id_establecimiento=${encodeURIComponent(idEstablecimiento)}` : ''}`;
        }
        window.location.href = url;
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