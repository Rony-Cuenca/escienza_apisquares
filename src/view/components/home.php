<?php
if (!function_exists('obtenerContextoActual')) {
  function obtenerContextoActual()
  {
    require_once __DIR__ . '/../../helpers/sesion_helper.php';
    $es_modo_directo = SesionHelper::esModoSuperAdmin();
    $es_superadmin = SesionHelper::esSuperAdmin();

    return [
      'es_modo_directo' => $es_modo_directo,
      'es_superadmin' => $es_superadmin,
      'establecimiento_id' => SesionHelper::obtenerEstablecimientoActual(),
      'usuario_id' => SesionHelper::obtenerUsuarioActual(),
      'cliente_id' => SesionHelper::obtenerClienteActual(),
      'rol' => $_SESSION['rol'] ?? ''
    ];
  }
}
?>

<div class="w-full bg-gray-200 flex-1 flex flex-col">
  <div class="max-w-screen-xl mx-auto w-full bg-white rounded-xl shadow-2xl shadow-gray-300/40 p-2 md:p-8">
    <!-- Cabecera con filtros -->
    <div class="flex flex-col items-center pt-6 pb-6 px-6 border-b border-gray-200 mb-8">
      <span class="text-center text-xl md:text-2xl text-gray-800 font-semibold mb-6" style="font-family: 'Montserrat', sans-serif;">DASHBOARD DE VENTAS</span>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center gap-6 w-full">
        <div class="flex flex-col w-full sm:w-auto">
          <label class="block text-base font-semibold text-slate-700 mb-2" for="select-establecimiento">Establecimiento</label>
          <select id="select-establecimiento" class="border border-gray-300 rounded-lg px-4 py-3 text-base w-full md:w-[400px] sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white shadow-sm transition duration-200">
            <?php foreach ($establecimientos as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['etiqueta']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex flex-col w-full sm:w-auto">
          <label class="block text-base font-semibold text-slate-700 mb-2" for="select-anio">Año</label>
          <select id="select-anio" class="border border-gray-300 rounded-lg px-4 py-3 text-base w-full sm:w-40 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white shadow-sm transition duration-200">
            <?php foreach ($anios as $anio): ?>
              <option value="<?= $anio ?>"><?= $anio ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Layout principal de gráficos -->
    <div class="flex flex-col md:flex-row gap-8 w-full">

      <!-- Fila 1: Resumen de ventas -->
      <div class="w-full md:w-1/2 min-w-0">
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 shadow-lg overflow-hidden h-full flex flex-col">
          <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-gray-200">
            <h1 class="text-lg md:text-xl font-semibold text-gray-800 text-center">RESUMEN DE VENTAS</h1>
          </div>
          <div class="p-4 flex-1 flex flex-col justify-center">
            <div class="flex flex-wrap items-center justify-center gap-4 mb-4">
              <div class="flex items-center space-x-2 bg-white px-3 py-1 rounded-lg shadow-sm">
                <span class="inline-block w-4 h-4 rounded-full bg-blue-600 shadow-sm"></span>
                <span class="text-sm font-medium text-gray-700">NUBOX360</span>
              </div>
              <div class="flex items-center space-x-2 bg-white px-3 py-1 rounded-lg shadow-sm">
                <span class="inline-block w-4 h-4 rounded-full bg-pink-500 shadow-sm"></span>
                <span class="text-sm font-medium text-gray-700">EDSUITE</span>
              </div>
              <div class="flex items-center space-x-2 bg-white px-3 py-1 rounded-lg shadow-sm">
                <span class="inline-block w-4 h-4 rounded-full bg-green-500 shadow-sm"></span>
                <span class="text-sm font-medium text-gray-700">SIRE</span>
              </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-100 p-2 overflow-hidden w-full">
              <div class="overflow-x-auto" style="width: 100%;">
                <div id="columnchart_material" class="w-[1200px] h-[380px]"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Fila 2: Variación mensual -->
      <div class="w-full md:w-1/2 min-w-0">
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 shadow-lg overflow-hidden h-full flex flex-col">
          <div class="px-4 py-3 bg-gradient-to-r from-green-50 to-green-100 border-b border-gray-200">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800 text-center">VARIACIÓN MENSUAL</h2>
          </div>
          <div class="p-4 flex-1 flex flex-col justify-center">
            <?php require_once "graficos/variacion.php" ?>
          </div>
        </div>
      </div>
    </div>
    <div class="flex flex-col items-center pt-6 pb-6 px-6 border-b border-gray-200 mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center gap-6 w-full">

        <div class="flex flex-col w-full sm:w-auto">
          <label class="block text-sm font-medium text-gray-600 mb-1">Tipo</label>
          <select id="select-tipo" class="border border-gray-300 rounded-md px-6 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white">
            <option value="NUBOX360">NUBOX360</option>
            <option value="SIRE">SIRE</option>
            <option value="EDSUITE">EDSUITE</option>
          </select>
        </div>
        <div class="flex flex-col w-full sm:w-auto">
          <label class="block text-sm font-medium text-gray-600 mb-1">Mes</label>
          <select id="select-mes" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white">
            <?php
            $meses = [
              '01' => 'Enero',
              '02' => 'Febrero',
              '03' => 'Marzo',
              '04' => 'Abril',
              '05' => 'Mayo',
              '06' => 'Junio',
              '07' => 'Julio',
              '08' => 'Agosto',
              '09' => 'Septiembre',
              '10' => 'Octubre',
              '11' => 'Noviembre',
              '12' => 'Diciembre'
            ];
            $mesActual = date('n');
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
      </div>

    </div>
    <!-- Fila 3: Gráficos secundarios en columnas responsivas -->
    <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="w-full min-h-[400px] max-h-[500px] bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-gray-200">
          <h3 class="text-base font-semibold text-gray-800 text-center">ANÁLISIS POR CATEGORÍAS</h3>
        </div>
        <div class="p-6 h-[calc(100%-4rem)] overflow-hidden">
          <?php require_once "graficos/pastel.php" ?>
        </div>
      </div>
      <div class="w-full min-h-[400px] max-h-[500px] bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-gray-200">
          <h3 class="text-base font-semibold text-gray-800 text-center">EXONERACIONES</h3>
        </div>
        <div class="p-6 h-[calc(100%-4rem)] overflow-hidden">
          <?php require_once "graficos/exoneracion.php" ?>
        </div>
      </div>
      <div class="w-full min-h-[400px] max-h-[500px] bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-teal-100 border-b border-gray-200">
          <h3 class="text-base font-semibold text-gray-800 text-center">PROMEDIO MENSUAL</h3>
        </div>
        <div class="p-6 h-[calc(100%-4rem)] overflow-hidden">
          <?php require_once "graficos/promedio.php" ?>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  function initializeGoogleCharts() {
    if (typeof google !== 'undefined' && google.charts) {
      console.log('Google Charts disponible, inicializando...');
      google.charts.load('current', {
        'packages': ['bar', 'corechart']
      });
      google.charts.setOnLoadCallback(function() {
        console.log('Google Charts cargado completamente');
        drawChart();
      });
    } else {
      console.log('Google Charts no disponible, reintentando...');
      setTimeout(initializeGoogleCharts, 100);
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    initializeGoogleCharts();
  });

  // --- GRÁFICO DE BARRAS ---
  function drawChart() {
    if (typeof google === 'undefined' || !google.charts) {
      console.log('Google Charts no está disponible aún');
      return;
    }

    let anio = document.getElementById('select-anio').value;
    let establecimiento = document.getElementById('select-establecimiento').value;

    console.log('Cargando datos del resumen de ventas...');

    fetch(`index.php?controller=home&action=resumenVentas&establecimiento=${establecimiento}&anio=${anio}`)
      .then(r => {
        if (!r.ok) {
          throw new Error(`HTTP error! status: ${r.status}`);
        }
        return r.json();
      })
      .then(datos => {
        console.log('Datos resumen ventas recibidos:', datos);

        let meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        let tipos = ['NUBOX360', 'EDSUITE', 'SIRE'];
        let data = [
          ['MES', ...tipos]
        ];
        meses.forEach(mes => {
          let nombreMes = new Date(0, mes - 1).toLocaleString('es-ES', {
            month: 'long'
          }).toUpperCase();
          let fila = [nombreMes];
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
            subtitle: ''
          },
          colors: ['#2563eb', '#ec4899', '#22c55e'],
          legend: {
            position: 'none'
          },
          width: 1200,
          height: 380,
          bars: 'vertical',
          bar: { groupWidth: '70%' },
          chartArea: {
            left: 50,
            right: 50,
            top: 20,
            bottom: 50,
            width: '100%',
            height: '80%'
          }
        };
        var chart = new google.charts.Bar(document.getElementById('columnchart_material'));
        chart.draw(chartData, google.charts.Bar.convertOptions(options));
      })
      .catch(error => {
        console.error('Error al cargar datos del resumen de ventas:', error);
      });
  }

  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('select-anio').addEventListener('change', drawChart);
    document.getElementById('select-establecimiento').addEventListener('change', drawChart);
  });

  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, iniciando gráficos...');

    setTimeout(function() {
      if (typeof drawPieChart === 'function') {
        console.log('Ejecutando drawPieChart');
        try {
          drawPieChart();
          document.getElementById('select-tipo').addEventListener('change', function() {
            drawPieChart();
            if (typeof drawPromedioVentaChart === 'function') drawPromedioVentaChart();
          });
          document.getElementById('select-mes').addEventListener('change', function() {
            drawPieChart();
            if (typeof drawPromedioVentaChart === 'function') drawPromedioVentaChart();
          });
        } catch (error) {
          console.error('Error al ejecutar drawPieChart:', error);
        }
      } else {
        console.log('drawPieChart no está disponible');
      }

      // Gráfico de exoneración
      if (typeof drawExoneracionChart === 'function') {
        console.log('Ejecutando drawExoneracionChart');
        try {
          drawExoneracionChart();
        } catch (error) {
          console.error('Error al ejecutar drawExoneracionChart:', error);
        }
      } else {
        console.log('drawExoneracionChart no está disponible');
      }

      // Gráfico de promedio
      if (typeof drawPromedioVentaChart === 'function') {
        console.log('Ejecutando drawPromedioVentaChart');
        try {
          drawPromedioVentaChart();
        } catch (error) {
          console.error('Error al ejecutar drawPromedioVentaChart:', error);
        }
      } else {
        console.log('drawPromedioVentaChart no está disponible');
      }

      // Gráfico de variación
      if (typeof drawVariacionVentasChart === 'function') {
        console.log('Ejecutando drawVariacionVentasChart');
        try {
          drawVariacionVentasChart();
          document.getElementById('select-tipo-vari').addEventListener('change', drawVariacionVentasChart);
        } catch (error) {
          console.error('Error al ejecutar drawVariacionVentasChart:', error);
        }
      } else {
        console.log('drawVariacionVentasChart no está disponible');
      }
    }, 1000);
    // Event listeners para filtros principales
    document.getElementById('select-anio').addEventListener('change', function() {
      console.log('Cambió año, actualizando gráficos...');
      setTimeout(function() {
        if (typeof drawExoneracionChart === 'function') drawExoneracionChart();
        if (typeof drawPromedioVentaChart === 'function') drawPromedioVentaChart();
        if (typeof drawVariacionVentasChart === 'function') drawVariacionVentasChart();
      }, 100);
    });

    document.getElementById('select-establecimiento').addEventListener('change', function() {
      console.log('Cambió establecimiento, actualizando gráficos...');
      setTimeout(function() {
        if (typeof drawExoneracionChart === 'function') drawExoneracionChart();
        if (typeof drawPromedioVentaChart === 'function') drawPromedioVentaChart();
        if (typeof drawVariacionVentasChart === 'function') drawVariacionVentasChart();
      }, 100);
    });
  });
</script>