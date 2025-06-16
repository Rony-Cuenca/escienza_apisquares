<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
  <div class="w-full mx-auto mt-8 bg-white rounded-xl shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-bold text-gray-800">RESUMEN DE VENTAS</h1>
      <div class="flex items-center space-x-3">
        <select id="select-anio" class="border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
          <?php foreach ($anios as $anio): ?>
            <option value="<?= $anio ?>"><?= $anio ?></option>
          <?php endforeach; ?>
        </select>
        <select id="select-sucursal" class="border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
          <?php foreach ($sucursales as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['razon_social']) ?></option>
          <?php endforeach; ?>
        </select>
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

    <div id="columnchart_material" class="w-full h-[350px]"></div>
  </div>
</div>


<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
  google.charts.load('current', {
    'packages': ['bar']
  });
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
    let anio = document.getElementById('select-anio').value;
    let sucursal = document.getElementById('select-sucursal').value;
    fetch(`index.php?controller=home&action=resumenVentas&sucursal=${sucursal}&anio=${anio}`)
      .then(r => r.json())
      .then(datos => {
        let meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        let tipos = ['NUBOX360', 'EDSUITE', 'SIRE'];
        let data = [
          ['MES', ...tipos]
        ];
        meses.forEach(mes => {
          let fila = [new Date(0, mes - 1).toLocaleString('es-ES', {
            month: 'long'
          })];
          tipos.forEach(tipo => {
            let found = datos.find(d => d.mes === mes && d.tipo === tipo);
            fila.push(found ? parseFloat(found.total) : 0);
          });
          data.push(fila);
        });

        var chartData = google.visualization.arrayToDataTable(data);
        var options = {
          chart: {
            title: '',
            subtitle: '',
          },
          colors: ['#2563eb', '#ec4899', '#22c55e'],
          legend: {
            position: 'none'
          }
        };
        var chart = new google.charts.Bar(document.getElementById('columnchart_material'));
        chart.draw(chartData, google.charts.Bar.convertOptions(options));
      });
  }

  document.getElementById('select-anio').addEventListener('change', drawChart);
  document.getElementById('select-sucursal').addEventListener('change', drawChart);
</script>