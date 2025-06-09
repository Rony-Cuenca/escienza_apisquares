<h1>RESUMEN DE VENTAS</h1>
<!-- Selects -->
<select id="select-anio">
  <?php foreach ($anios as $anio): ?>
    <option value="<?= $anio ?>"><?= $anio ?></option>
  <?php endforeach; ?>
</select>
<select id="select-sucursal">
  <?php foreach ($sucursales as $s): ?>
    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['razon_social']) ?></option>
  <?php endforeach; ?>
</select>

<div id="columnchart_material" style="width: 800px; height: 500px;"></div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
google.charts.load('current', {'packages':['bar']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
  let anio = document.getElementById('select-anio').value;
  let sucursal = document.getElementById('select-sucursal').value;
  fetch(`index.php?controller=home&action=resumenVentas&sucursal=${sucursal}&anio=${anio}`)
    .then(r => r.json())
    .then(datos => {
      // Procesar datos para Google Charts
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
        chart: {
          title: 'Resumen de Ventas',
          subtitle: 'Por mes y sistema',
        }
      };
      var chart = new google.charts.Bar(document.getElementById('columnchart_material'));
      chart.draw(chartData, google.charts.Bar.convertOptions(options));
    });
}

// Eventos para recargar el gráfico
document.getElementById('select-anio').addEventListener('change', drawChart);
document.getElementById('select-sucursal').addEventListener('change', drawChart);
</script>
