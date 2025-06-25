
<div class="bg-white rounded-xl shadow p-4 sm:p-6 w-full md:w-[900px] mb-8">
    <select id="select-tipo-vari" class="border rounded px-2 py-1 text-sm w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-blue-400 mb-4">
        <option value="">Selecciona una opción</option>
        <option value="NUBOX360">Nubox</option>
        <option value="SIRE">SIRE</option>
        <option value="EDSUITE">EDSuite</option>
    </select>
    <h1 class="text-xl font-bold text-gray-800 mb-6">VENTAS RESPECTO AL MES ANTERIOR</h1>
    <div class="overflow-x-auto pb-2">
        <div style="width:100vw; min-width:600px; max-width:900px;">
            <canvas id="variacionVentasChart" height="220" style="width:100% !important; min-width:600px; max-width:900px;"></canvas>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
function drawVariacionVentasChart() {
    let anio = document.getElementById('select-anio').value;
  let tipovar = document.getElementById('select-tipo-vari').value;

    let sucursal = document.getElementById('select-sucursal').value;
    fetch(`index.php?controller=home&action=variacionVentasMensual&sucursal=${sucursal}&anio=${anio}&tipo=${tipovar}`)
        .then(r => r.json())
        .then(datos => {
            const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
            const variaciones = datos.map(d => d.variacion);
            const totales = datos.map(d => d.total);
            const colores = variaciones.map(v => v === null ? '#a3a3a3' : v >= 0 ? '#22c55e' : '#ef4444');
            // Destruir gráfico anterior si existe
            if(window.variacionVentasChartObj) window.variacionVentasChartObj.destroy();
            const ctx = document.getElementById('variacionVentasChart').getContext('2d');
            window.variacionVentasChartObj = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: '% Variación mensual',
                            data: variaciones,
                            fill: true,
                            backgroundColor: function(context) {
                                const v = context.dataset.data[context.dataIndex];
                                if (v === null) return 'rgba(163,163,163,0.1)';
                                return v >= 0 ? 'rgba(34,197,94,0.15)' : 'rgba(239,68,68,0.15)';
                            },
                            borderColor: '#2563eb',
                            pointBackgroundColor: colores,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            tension: 0.3,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Total de ventas',
                            data: totales,
                            type: 'bar',
                            backgroundColor: 'rgba(37,99,235,0.15)',
                            borderColor: '#2563eb',
                            borderWidth: 1,
                            yAxisID: 'y1',
                            order: 0
                        }
                    ]
                },
                options: {
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    if (ctx.dataset.label === '% Variación mensual') {
                                        return ctx.parsed.y === null ? 'Sin dato' : `% Variación: ${ctx.parsed.y}%`;
                                    } else {
                                        return `Total ventas: S/ ${ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            title: { display: true, text: '% variación' },
                            beginAtZero: true,
                            position: 'left'
                        },
                        y1: {
                            title: { display: true, text: 'Total de ventas (S/)' },
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false }
                        }
                    }
                },
                plugins: [{
                    // Etiquetas sobre cada punto de variación
                    afterDatasetsDraw: function(chart) {
                        const ctx = chart.ctx;
                        const ds = chart.data.datasets[0];
                        const meta = chart.getDatasetMeta(0);
                        meta.data.forEach(function(point, index) {
                            const value = ds.data[index];
                            if (value !== null && typeof value !== 'undefined') {
                                ctx.save();
                                ctx.font = 'bold 12px sans-serif';
                                ctx.fillStyle = value >= 0 ? '#22c55e' : '#ef4444';
                                ctx.textAlign = 'center';
                                ctx.fillText(value + '%', point.x, point.y - 10);
                                ctx.restore();
                            }
                        });
                        // Etiquetas sobre las barras de total de ventas
                        const dsBar = chart.data.datasets[1];
                        const metaBar = chart.getDatasetMeta(1);
                        metaBar.data.forEach(function(bar, index) {
                            const value = dsBar.data[index];
                            if (value !== null && typeof value !== 'undefined') {
                                ctx.save();
                                ctx.font = 'bold 12px sans-serif';
                                ctx.fillStyle = '#2563eb';
                                ctx.textAlign = 'center';
                                ctx.fillText('S/ ' + value.toLocaleString(undefined, {maximumFractionDigits:0}), bar.x, bar.y - 10);
                                ctx.restore();
                            }
                        });
                    }
                }]
            });
        });
}
document.getElementById('select-anio').addEventListener('change', drawVariacionVentasChart);
document.getElementById('select-tipo-vari').addEventListener('change', drawVariacionVentasChart);

document.getElementById('select-sucursal').addEventListener('change', drawVariacionVentasChart);
window.addEventListener('DOMContentLoaded', drawVariacionVentasChart);
    </script>
</div>