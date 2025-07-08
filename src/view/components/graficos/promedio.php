<div class="w-full h-full flex items-center justify-center min-h-[200px] max-h-[300px] overflow-hidden">
  <canvas id="promedioVentaChart" style="width: 100%; height: 100%; min-height: 200px; max-height: 300px;"></canvas>
</div>
    <script>
function drawPromedioVentaChart() {
    let anio = document.getElementById('select-anio').value;
    let establecimiento = document.getElementById('select-establecimiento').value;
    
    // Verificar si existen los elementos antes de usarlos
    let mesElement = document.getElementById('select-mes');
    let tipoElement = document.getElementById('select-tipo');
    
    let mes = mesElement ? mesElement.value : '06'; // Valor por defecto
    let tipo = tipoElement ? tipoElement.value : 'NUBOX360'; // Valor por defecto
    
    console.log('drawPromedioVentaChart - Año:', anio, 'Establecimiento:', establecimiento, 'Mes:', mes, 'Tipo:', tipo);
    
    fetch(`index.php?controller=home&action=promedioVentaPorSerie&establecimiento=${establecimiento}&anio=${anio}&mes=${mes}&tipo=${tipo}`)
        .then(r => r.json())
        .then(datos => {
            console.log('Datos promedio recibidos:', datos);
            
            if (!datos.length) {
                console.log('No hay datos para el gráfico de promedio');
                if(window.promedioVentaChartObj) window.promedioVentaChartObj.destroy();
                
                const ctx = document.getElementById('promedioVentaChart').getContext('2d');
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = '16px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('No hay datos disponibles', ctx.canvas.width/2, ctx.canvas.height/2);
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
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                           // text: 'Promedio de Venta por Comprobante (por Serie)',
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
        })
        .catch(error => {
            console.error('Error al cargar datos de promedio:', error);
        });
}
</script>

</script>
