<!-- //Record exoneracion de IGV with chart js -->
<div class="bg-white rounded-xl shadow p-4 sm:p-6 mt-8">
  <h1 class="text-xl font-bold text-gray-800 mb-4">RECORD EXONERACIÓN DE IGV</h1>
  <div class="overflow-x-auto">
  <canvas id="exoneracionChart" width="500" height="400"></canvas>

  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</div>

<script>
    // --- GRÁFICO DE EXONERACIÓN ---
function drawExoneracionChart() {
  let anio = document.getElementById('select-anio').value;
  let establecimiento = document.getElementById('select-establecimiento').value;
  fetch(`index.php?controller=home&action=exoneracionIGV&establecimiento=${establecimiento}&anio=${anio}`)
    .then(r => r.json())
    .then(datos => {
      if (!datos.length) {
        document.getElementById('exoneracionChart').getContext('2d').clearRect(0,0,400,200);
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
    });
}

// Llama al cargar y al cambiar filtros
document.getElementById('select-anio').addEventListener('change', drawExoneracionChart);
document.getElementById('select-establecimiento').addEventListener('change', drawExoneracionChart);
window.addEventListener('DOMContentLoaded', drawExoneracionChart);
</script>
