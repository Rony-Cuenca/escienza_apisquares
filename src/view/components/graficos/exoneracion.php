<!-- Record exoneracion de IGV with chart js -->
<div class="w-full h-full flex items-center justify-center min-h-[200px] max-h-[300px] overflow-hidden">
  <canvas id="exoneracionChart" style="width: 100%; height: 100%; min-height: 200px; max-height: 300px;"></canvas>
</div>

<script>
    // --- GRÁFICO DE EXONERACIÓN ---
function drawExoneracionChart() {
  let anio = document.getElementById('select-anio').value;
  let establecimiento = document.getElementById('select-establecimiento').value;
  
  console.log('drawExoneracionChart - Año:', anio, 'Establecimiento:', establecimiento);
  
  fetch(`index.php?controller=home&action=exoneracionIGV&establecimiento=${establecimiento}&anio=${anio}`)
    .then(r => r.json())
    .then(datos => {
      console.log('Datos exoneración recibidos:', datos);
      
      if (!datos.length) {
        console.log('No hay datos para el gráfico de exoneración');
        const ctx = document.getElementById('exoneracionChart').getContext('2d');
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
        ctx.font = '16px Arial';
        ctx.fillStyle = '#666';
        ctx.textAlign = 'center';
        ctx.fillText('No hay datos disponibles', ctx.canvas.width/2, ctx.canvas.height/2);
        return;
      }
      const series = datos.map(d => d.serie);
      const porcentajes = datos.map(d => d.porcentaje);
      const max = Math.max(...porcentajes);
      const min = Math.min(...porcentajes);

      const colores = porcentajes.map(p =>
        p === max ? '#2563eb' :
        p === min ? '#ef4444' :
        '#a3a3a3'
      );

      // Destruir gráfico anterior si existe
      if(window.exoneracionChartObj) window.exoneracionChartObj.destroy();

      const ctx = document.getElementById('exoneracionChart').getContext('2d');
      window.exoneracionChartObj = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: series,
          datasets: [{
            label: '% Exoneración IGV',
            data: porcentajes,
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
             // text: 'Porcentaje de Exoneración de IGV por Serie',
              font: { size: 18 }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return `${context.dataset.label}: ${context.parsed.y}%`;
                }
              }
            },
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              title: { display: true, text: '% Exoneración' }
            },
            x: {
              title: { display: true, text: 'Serie' }
            }
          }
        }
      });
    })
    .catch(error => {
      console.error('Error al cargar datos de exoneración:', error);
    });
}
</script>
