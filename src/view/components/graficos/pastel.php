<!-- Gráfico de Pastel -->
<div class="w-full h-full flex flex-col max-h-[400px]">
    <div class="flex flex-col gap-3 mb-4 flex-shrink-0">
      <div class="flex flex-col">
        <label class="block text-sm font-medium text-gray-600 mb-1">Tipo</label>
        <select id="select-tipo" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white">
          <option value="NUBOX360">NUBOX360</option>
          <option value="SIRE">SIRE</option>
          <option value="EDSUITE">EDSUITE</option>
        </select>
      </div>
      <div class="flex flex-col">
        <label class="block text-sm font-medium text-gray-600 mb-1">Mes</label>
        <select id="select-mes" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white">
        <?php
          $meses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
          ];
          // Calcular el mes anterior
          $mesActual = date('n'); // 1-12
          $mesAnterior = $mesActual - 1;
          if ($mesAnterior == 0) {
            $mesAnterior = 12;
          }
          $mesAnteriorStr = str_pad($mesAnterior, 2, '0', STR_PAD_LEFT);

          foreach ($meses as $num => $nombre) {
            $selected = ($num == $mesAnteriorStr) ? 'selected' : '';
            echo "<option value=\"$num\" $selected>$nombre</option>";
          }
        ?>
      </select>
    </div>
    <div class="flex-1 w-full flex items-center justify-center min-h-[200px] max-h-[250px] overflow-hidden">
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

