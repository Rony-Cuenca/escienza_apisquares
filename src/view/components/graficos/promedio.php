<div id="tablaPromedioVenta" class="mt-4"></div>

<div class="bg-white rounded-xl shadow p-4 sm:p-6 mt-8">
  <h1 class="text-xl font-bold text-gray-800 mb-4">PROMEDIO DE VENTA POR COMPROBANTE (SERIE)</h1>
  <canvas id="promedioVentaChart" height="200"></canvas>
    <script>
function drawPromedioVentaChart() {
    let anio = document.getElementById('select-anio').value;
    let sucursal = document.getElementById('select-sucursal').value;
    let mes = document.getElementById('select-mes').value;
  let tipo = document.getElementById('select-tipo').value;
    fetch(`index.php?controller=home&action=promedioVentaPorSerie&sucursal=${sucursal}&anio=${anio}&mes=${mes}&tipo=${tipo}`)
        .then(r => r.json())
        .then(datos => {
            if (!datos.length) {
                if(window.promedioVentaChartObj) window.promedioVentaChartObj.destroy();
                return;
            }
            const series = datos.map(d => d.serie);
            const promedios = datos.map(d => d.promedio);
            const totales = datos.map(d => d.total_vendido);
            const cantidades = datos.map(d => d.total_comprobantes);

            // Colores para barras
            const colores = series.map(() => '#2563eb');

            // Destruir gráfico anterior si existe
            if(window.promedioVentaChartObj) window.promedioVentaChartObj.destroy();

            const ctx = document.getElementById('promedioVentaChart').getContext('2d');
            window.promedioVentaChartObj = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: series,
                    datasets: [{
                        label: 'Promedio S/ por comprobante',
                        data: promedios,
                        backgroundColor: colores,
                        borderRadius: 8,
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Promedio de Venta por Comprobante (por Serie)',
                            font: { size: 18 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    return [
                                        `Promedio: S/ ${promedios[idx]}`,
                                        `Total vendido: S/ ${totales[idx]}`,
                                        `Comprobantes: ${cantidades[idx]}`
                                    ];
                                }
                            }
                        },
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Promedio S/ por comprobante' }
                        },
                        x: {
                            title: { display: true, text: 'Serie' }
                        }
                    }
                }
            });
        });
}
document.getElementById('select-anio').addEventListener('change', drawPromedioVentaChart);
document.getElementById('select-sucursal').addEventListener('change', drawPromedioVentaChart);
document.getElementById('select-mes').addEventListener('change', drawPromedioVentaChart);
document.getElementById('select-tipo').addEventListener('change', drawPromedioVentaChart);
window.addEventListener('DOMContentLoaded', drawPromedioVentaChart);
</script>
<div id="tablaPromedioVenta" class="mt-4"></div>
<script>
function drawTablaPromedioVenta() {
    let anio = document.getElementById('select-anio').value;
    let sucursal = document.getElementById('select-sucursal').value;
    fetch(`index.php?controller=home&action=promedioVentaPorSerie&sucursal=${sucursal}&anio=${anio}&mes=${mes}&tipo=${tipo}`)
        .then(r => r.json())
        .then(datos => {
            let html = '<table class="min-w-full text-sm text-left border mt-2"><thead><tr><th class="border px-2">Serie</th><th class="border px-2">Total vendido</th><th class="border px-2">Comprobantes</th><th class="border px-2">Promedio</th></tr></thead><tbody>';
            datos.forEach(d => {
                html += `<tr>
                    <td class="border px-2">${d.serie}</td>
                    <td class="border px-2">S/ ${parseFloat(d.total_vendido).toLocaleString()}</td>
                    <td class="border px-2">${d.total_comprobantes}</td>
                    <td class="border px-2">S/ ${parseFloat(d.promedio).toLocaleString()}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('tablaPromedioVenta').innerHTML = html;
        });
}
document.getElementById('select-anio').addEventListener('change', drawTablaPromedioVenta);
document.getElementById('select-sucursal').addEventListener('change', drawTablaPromedioVenta);
document.getElementById('select-mes').addEventListener('change', drawTablaPromedioVenta);
document.getElementById('select-tipo').addEventListener('change', drawTablaPromedioVenta);
window.addEventListener('DOMContentLoaded', drawTablaPromedioVenta);
</script>