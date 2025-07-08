<!-- Gráfico de Pastel -->
<div class="w-full h-full flex flex-row items-stretch max-h-[400px] gap-4">
  <!-- Selectores a la izquierda -->
  
  <!-- Gráfico a la derecha -->
  <div class="flex-1 flex items-center justify-center min-h-[200px] max-h-[250px] overflow-hidden">
    <div id="piechart" style="width: 100%; height: 100%; min-height: 200px; max-height: 250px;"></div>
  </div>
</div>

<script>
// --- GRÁFICO DE PASTEL ---
function drawPieChart() {
  // Verificar que Google Charts esté disponible
  if (typeof google === 'undefined' || !google.charts) {
    console.log('Google Charts no disponible para el gráfico de pastel');
    setTimeout(drawPieChart, 500);
    return;
  }

  let anio = document.getElementById('select-anio').value;
  let establecimiento = document.getElementById('select-establecimiento').value;
  let mes = document.getElementById('select-mes').value;
  let tipo = document.getElementById('select-tipo').value;
  
  console.log('drawPieChart - Datos:', {anio, establecimiento, mes, tipo});
  
  fetch(`index.php?controller=home&action=seriesMasVendidas&establecimiento=${establecimiento}&anio=${anio}&mes=${mes}&tipo=${tipo}`)
    .then(r => {
      if (!r.ok) {
        throw new Error(`HTTP error! status: ${r.status}`);
      }
      return r.json();
    })
    .then(datos => {
      console.log('Datos pastel recibidos:', datos);
      
      if (!datos || datos.length === 0) {
        console.log('No hay datos para el gráfico de pastel');
        // Mostrar mensaje de no datos
        const container = document.getElementById('piechart');
        container.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 16px;">No hay datos disponibles</div>';
        return;
      }
      
      let dataArray = [['Serie', 'Total']];
      datos.forEach(d => dataArray.push([d.serie, parseFloat(d.total)]));
      var data = google.visualization.arrayToDataTable(dataArray);

      var options = {
        title: '',
        pieHole: 0.4,
        legend: { position: 'bottom', textStyle: { fontSize: 12 } },
        chartArea: { left: 10, top: 10, width: '100%', height: '80%' }
      };

      var chart = new google.visualization.PieChart(document.getElementById('piechart'));
      chart.draw(data, options);
    })
    .catch(error => {
      console.error('Error al cargar datos del gráfico de pastel:', error);
      const container = document.getElementById('piechart');
      container.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 16px;">Error al cargar datos</div>';
    });
}
</script>

