<div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-8">
  <div class="flex flex-col items-center">
    <span class="text-center text-base text-slate-700 font-semibold mb-2">Filtrar datos históricos</span>
    <hr class="w-full mb-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center gap-4 sm:gap-8 w-full">
      <div class="flex flex-col w-full sm:w-auto">
        <label class="block text-base font-semibold text-slate-700 mb-1" for="select-sucursal">Establecimiento</label>
        <select id="select-sucursal" class="border border-gray-300 rounded-lg px-4 py-2 text-base w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
          <?php foreach ($sucursales as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['razon_social']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex flex-col w-full sm:w-auto">
        <label class="block text-base font-semibold text-slate-700 mb-1" for="select-anio">Año</label>
        <select id="select-anio" class="border border-gray-300 rounded-lg px-4 py-2 text-base w-full sm:w-40 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
          <?php foreach ($anios as $anio): ?>
            <option value="<?= $anio ?>"><?= $anio ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>
</div>

<div class="flex flex-col gap-8 mt-8 md:flex-row md:justify-center">
  <!-- Gráfico de Barras -->
  <div class="bg-white rounded-xl shadow p-4 sm:p-6 w-full md:w-[700px]">
      <h1 class="text-xl font-bold text-gray-800">RESUMEN DE VENTAS</h1>

    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-3">
        <!-- Selectores ocultos, ya están arriba -->
      </div>
    </div>
    <div class="flex items-center space-x-6 mb-2">
      <div class="flex items-center space-x-2">
        <span class="inline-block w-3 h-3 rounded-full bg-blue-600"></span>
        <span class="text-xs text-gray-700">Nubox</span>
      </div>
      <div class="flex items-center space-x-2">
        <span class="inline-block w-3 h-3 rounded-full bg-pink-500"></span>
        <span class="text-xs text-gray-700">EDSuite</span>
      </div>
      <div class="flex items-center space-x-2">
        <span class="inline-block w-3 h-3 rounded-full bg-green-500"></span>
        <span class="text-xs text-gray-700">SIRE</span>
      </div>
    </div>
    <!-- Contenedor con scroll horizontal en móvil -->
    <div class="overflow-x-auto">
      <div id="columnchart_material" class="min-w-[700px] h-[350px]"></div>
    </div>
  </div>

  <!-- Gráfico de Pastel -->
  <div class="bg-white rounded-xl shadow p-4 sm:p-6 w-full md:w-[500px] flex flex-col items-center">
    <h1 class="text-2xl font-bold text-center mb-4">SERIES CON MÁS VENTA</h1>
    <div class="flex flex-col sm:flex-row items-center justify-center mb-2 gap-2 w-full">
      <select id="select-tipo" class="border rounded px-2 py-1 text-sm w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="">Selecciona una opción</option>
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
          foreach ($meses as $num => $nombre) {
            echo "<option value=\"$num\">$nombre</option>";
          }
        ?>
      </select>
    </div>
    <div id="piechart" style="width: 100%; max-width: 400px; height: 320px;"></div>
  </div>
</div>

<!-- Google Charts Scripts -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
// --- GRÁFICO DE BARRAS ---
google.charts.load('current', {'packages':['bar']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
  let anio = document.getElementById('select-anio').value;
  let sucursal = document.getElementById('select-sucursal').value;
  fetch(`index.php?controller=home&action=resumenVentas&sucursal=${sucursal}&anio=${anio}`)
    .then(r => r.json())
    .then(datos => {
      let meses = ['01','02','03','04','05','06','07','08','09','10','11','12'];
      let tipos = ['NUBOX360','EDSUITE','SIRE'];
      let data = [['MES', ...tipos]];
      meses.forEach(mes => {
        let fila = [new Date(0, mes-1).toLocaleString('es-ES', {month:'long'})];
        tipos.forEach(tipo => {
          let found = datos.find(d => d.mes === mes && d.tipo === tipo);
          fila.push(found ? parseFloat(found.total) : 0);
        });
        data.push(fila);
      });

      var chartData = google.visualization.arrayToDataTable(data);
      var options = {
        chart: { title: '', subtitle: '' },
        colors: ['#2563eb', '#ec4899', '#22c55e'],
        legend: { position: 'none' }
      };
      var chart = new google.charts.Bar(document.getElementById('columnchart_material'));
      chart.draw(chartData, google.charts.Bar.convertOptions(options));
    });
}

document.getElementById('select-anio').addEventListener('change', drawChart);
document.getElementById('select-sucursal').addEventListener('change', drawChart);

// --- GRÁFICO DE PASTEL ---
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawPieChart);

function drawPieChart() {
  let anio = document.getElementById('select-anio').value;
  let sucursal = document.getElementById('select-sucursal').value;
  let mes = document.getElementById('select-mes').value;
  let tipo = document.getElementById('select-tipo').value;
  fetch(`index.php?controller=home&action=seriesMasVendidas&sucursal=${sucursal}&anio=${anio}&mes=${mes}&tipo=${tipo}`)
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

document.getElementById('select-sucursal').addEventListener('change', function() {
  document.getElementById('select-tipo').selectedIndex = 0;
  document.getElementById('select-mes').selectedIndex = 0;
  drawPieChart();
});
document.getElementById('select-mes').addEventListener('change', drawPieChart);
document.getElementById('select-tipo').addEventListener('change', drawPieChart);
</script>
