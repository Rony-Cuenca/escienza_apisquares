<div class="w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div class="flex flex-row items-center gap-2">
            <label class="block text-sm font-medium text-gray-700 mb-0">Tipo de Reporte</label>
            <select id="select-tipo-vari" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                <option value="NUBOX360">NUBOX360</option>
                <option value="SIRE">SIRE</option>
                <option value="EDSUITE">EDSUITE</option>
            </select>
        </div>
    </div>
    <div class="bg-white rounded-lg p-2 w-full">
        <div style="height: 380px">
            <canvas id="variacionVentasChart"></canvas>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
function drawVariacionVentasChart() {
    let anio = document.getElementById('select-anio').value;
    let tipovar = document.getElementById('select-tipo-vari').value;

    console.log('Cargando variación mensual:', {anio, tipo: tipovar});
    
    fetch(`index.php?controller=home&action=variacionVentasMensual&anio=${anio}&tipo=${tipovar}`)
        .then(r => r.json())
        .then(datos => {
            console.log('Datos recibidos:', datos);
            
            // Solo meses con datos
            const mesesNombres = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
            
            // No filtrar aquí, usar todos los datos recibidos
            const labels = datos.map(d => mesesNombres[parseInt(d.mes, 10) - 1]);
            const variaciones = datos.map(d => d.variacion === null || d.variacion === undefined ? 0 : d.variacion);
            const totales = datos.map(d => d.total);
            const colores = variaciones.map(v => v > 0 ? '#22c55e' : v < 0 ? '#ef4444' : '#2563eb');
            // Destruir gráfico anterior si existe
            if(window.variacionVentasChartObj) window.variacionVentasChartObj.destroy();
            const ctx = document.getElementById('variacionVentasChart').getContext('2d');
            window.variacionVentasChartObj = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
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
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 20,
                            right: 20,
                            top: 20,
                            bottom: 20
                        }
                    },
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
                                ctx.fillStyle = value > 0 ? '#22c55e' : value < 0 ? '#ef4444' : '#2563eb';
                                ctx.textAlign = 'center';
                                ctx.fillText((value === 0 ? '0%' : value + '%'), point.x, point.y - 10);
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
        })
        .catch(error => {
            console.error('Error al cargar datos de variación mensual:', error);
            // Destruir gráfico anterior si existe
            if(window.variacionVentasChartObj) window.variacionVentasChartObj.destroy();
        });
}
document.getElementById('select-anio').addEventListener('change', drawVariacionVentasChart);
document.getElementById('select-tipo-vari').addEventListener('change', drawVariacionVentasChart);

document.getElementById('select-establecimiento').addEventListener('change', drawVariacionVentasChart);
window.addEventListener('DOMContentLoaded', drawVariacionVentasChart);
    </script>
</div>