<!-- Gráfico de Pastel -->
  <div class="bg-white rounded-xl shadow p-4 sm:p-6 mt-8">
    <h1 class="text-xl font-bold text-center mb-4">SERIES CON MÁS VENTA</h1>
    <div class="flex flex-col sm:flex-row items-center justify-center mb-2 gap-2 w-full">
      <select id="select-tipo" class="border rounded px-2 py-1 text-sm w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="NUBOX360">Nubox</option>
        <option value="SIRE">SIRE</option>
        <option value="EDSUITE">EDSuite</option>
      </select>
      <select id="select-mes" class="border rounded px-2 py-1 text-sm w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-blue-400">
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
    <div id="piechart" style="width: 100%; max-width: 400px; height: 220px;"></div>
  </div>


  
  <script>
    // --- GRÁFICO DE PASTEL ---
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawPieChart);

function drawPieChart() {
  let anio = document.getElementById('select-anio').value;
  let establecimiento = document.getElementById('select-establecimiento').value;
  let mes = document.getElementById('select-mes').value;
  let tipo = document.getElementById('select-tipo').value;
  fetch(`index.php?controller=home&action=seriesMasVendidas&establecimiento=${establecimiento}&anio=${anio}&mes=${mes}&tipo=${tipo}`)
    .then(r => r.json())
    .then(datos => {
      let dataArray = [['Serie', 'Total']];
      datos.forEach(d => dataArray.push([d.serie, parseFloat(d.total)]));
      var data = google.visualization.arrayToDataTable(dataArray);

      var options = {
        title: 'Series con más ventas del mes',
        pieHole: 0.4,
        legend: { position: 'right' }
      };

      var chart = new google.visualization.PieChart(document.getElementById('piechart'));
      chart.draw(data, options);
    });
}

document.getElementById('select-establecimiento').addEventListener('change', function() {
  document.getElementById('select-tipo').selectedIndex = 0;

  // Seleccionar el mes anterior al actual
  const selectMes = document.getElementById('select-mes');
  const fecha = new Date();
  let mesAnterior = fecha.getMonth(); // 0-11, pero queremos 1-12
  if (mesAnterior === 0) mesAnterior = 12;
  const mesAnteriorStr = mesAnterior.toString().padStart(2, '0');
  for (let i = 0; i < selectMes.options.length; i++) {
    if (selectMes.options[i].value === mesAnteriorStr) {
      selectMes.selectedIndex = i;
      break;
    }
  }

  drawPieChart();
});
document.getElementById('select-mes').addEventListener('change', drawPieChart);
document.getElementById('select-tipo').addEventListener('change', drawPieChart);
// --- GRÁFICO DE PASTEL ---
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawPieChart);

function drawPieChart() {
  let anio = document.getElementById('select-anio').value;
  let establecimiento = document.getElementById('select-establecimiento').value;
  let mes = document.getElementById('select-mes').value;
  let tipo = document.getElementById('select-tipo').value;
  fetch(`index.php?controller=home&action=seriesMasVendidas&establecimiento=${establecimiento}&anio=${anio}&mes=${mes}&tipo=${tipo}`)
    .then(r => r.json())
    .then(datos => {
      let dataArray = [['Serie', 'Total']];
      datos.forEach(d => dataArray.push([d.serie, parseFloat(d.total)]));
      var data = google.visualization.arrayToDataTable(dataArray);

      var options = {
        title: 'Series con más ventas del mes',
        pieHole: 0.4,
        legend: { position: 'right' }
      };

      var chart = new google.visualization.PieChart(document.getElementById('piechart'));
      chart.draw(data, options);
    });
}

document.getElementById('select-establecimiento').addEventListener('change', function() {
  document.getElementById('select-tipo').selectedIndex = 0;

  // Seleccionar el mes anterior al actual
  const selectMes = document.getElementById('select-mes');
  const fecha = new Date();
  let mesAnterior = fecha.getMonth(); // 0-11, pero queremos 1-12
  if (mesAnterior === 0) mesAnterior = 12;
  const mesAnteriorStr = mesAnterior.toString().padStart(2, '0');
  for (let i = 0; i < selectMes.options.length; i++) {
    if (selectMes.options[i].value === mesAnteriorStr) {
      selectMes.selectedIndex = i;
      break;
    }
  }

  drawPieChart();
});
document.getElementById('select-mes').addEventListener('change', drawPieChart);
document.getElementById('select-tipo').addEventListener('change', drawPieChart);



  </script>
