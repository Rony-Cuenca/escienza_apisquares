<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
<div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-8">
  <div class="flex flex-col items-center">
    <span class="text-center text-base text-slate-700 font-semibold mb-2">Filtrar datos históricos</span>
    <hr class="w-full mb-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center gap-4 sm:gap-8 w-full">
      <div class="flex flex-col w-full sm:w-auto">
        <label class="block text-base font-semibold text-slate-700 mb-1" for="select-sucursal">Establecimiento</label>
        <select id="select-sucursal" class="border border-gray-300 rounded-lg px-4 py-2 text-base w-full md:w-[400px] sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
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
<!-- Google Charts Scripts -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<!-- Layout principal de gráficos -->
<div class="flex flex-col gap-8 mt-8">

  <!-- Fila 1: Resumen de ventas -->
  <div class="w-full flex flex-col items-center">
    <div class="bg-white rounded-xl shadow p-4 sm:p-6 w-full md:w-[900px]">
      <h1 class="text-xl font-bold text-gray-800">RESUMEN DE VENTAS</h1>
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
      <div class="overflow-x-auto">
        <div id="columnchart_material" class="min-w-[900px] h-[350px]" style="overflow-x: auto; white-space: nowrap;"></div>
      </div>
    </div>
  </div>

  <!-- Fila 2: Variación mensual -->
  <div class="w-full flex flex-col items-center">
  <?php require_once "graficos/variacion.php" ?>
</div>

  <!-- Fila 3: Gráficos secundarios en dos columnas -->
  <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-8">
  <div class="flex justify-center">
    <div class="w-full max-w-md">
      <?php require_once "graficos/pastel.php" ?>
    </div>
  </div>
  <div class="flex justify-center">
    <div class="w-full max-w-md">
      <?php require_once "graficos/exoneracion.php" ?>
    </div>
  </div>
  <div class="flex justify-center">
    <div class="w-full max-w-md">
      <?php require_once "graficos/promedio.php" ?>
    </div>
  </div>
</div>
</div>

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
        let nombreMes = new Date(0, mes-1).toLocaleString('es-ES', {month:'long'}).toUpperCase();
        let fila = [nombreMes];
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
</script>

