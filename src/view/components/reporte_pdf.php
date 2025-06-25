<?php
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain', 'Spanish');
date_default_timezone_set('America/Lima');
$meses = [
    'January' => 'Enero',
    'February' => 'Febrero',
    'March' => 'Marzo',
    'April' => 'Abril',
    'May' => 'Mayo',
    'June' => 'Junio',
    'July' => 'Julio',
    'August' => 'Agosto',
    'September' => 'Septiembre',
    'October' => 'Octubre',
    'November' => 'Noviembre',
    'December' => 'Diciembre'
];
foreach ($meses as $en => $es) {
    if (strpos($nombreMes, $en) !== false) {
        $nombreMes = str_replace($en, $es, $nombreMes);
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Cuadres</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            background: white;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        h3 {
            margin: 30px 0 10px 0;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        th,
        td {
            padding: 6px 8px;
            border: 1px solid #2563EB;
        }

        th {
            background: #A9C3E8;
            color: #222;
            text-align: left;
        }

        tr.bg-red {
            background: #dc3545;
            color: #fff;
        }

        tr.bg-total {
            background: #f3f4f6;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .rounded {
            border-radius: 12px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mb-8 {
            margin-bottom: 32px;
        }

        .border {
            border: 1px solid #2563EB;
        }

        .bg-white {
            background: #fff;
        }

        .bg-yellow {
            background: #fef9c3;
        }

        .bg-header {
            background: #A9C3E8;
        }

        @page {
            margin-bottom: 60px;
        }

        html {
            position: relative;
            min-height: 100%;
        }

        body {
            margin-bottom: 60px;
        }

        footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            color: #888;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- 1. DATOS GENERALES -->

    <div style="height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
        <img src="http://localhost/escienza_apisquares/public/img/logo.png" alt="Logo" style="width:120px; margin-bottom: 16px;">
        <h2 style="font-size: 2rem; margin-bottom: 24px;"><?= 'REPORTE DE CUADRES - ' . strtoupper(htmlspecialchars($nombreMes)) ?></h2>
        <div style="background: #f3f4f6; border-radius: 12px; padding: 24px 36px; box-shadow: 0 2px 8px #0001; min-width: 350px;">
            <p style="margin: 0 0 8px 0;"><strong>Fecha de generación:</strong> <?= date('d/m/Y H:i:s') ?></p>
            <p style="margin: 0 0 8px 0;"><strong>Usuario:</strong> <?= htmlspecialchars($usuarioNombre) ?></p>
            <p style="margin: 0 0 8px 0;"><strong>RUC:</strong> <?= htmlspecialchars($rucSucursal) ?></p>
            <p style="margin: 0 0 8px 0;"><strong>Razón Social:</strong> <?= htmlspecialchars($nombreSucursal) ?></p>
            <?php if (!empty($direccionSucursal)): ?>
                <p style="margin: 0 0 8px 0;"><strong>Dirección:</strong> <?= htmlspecialchars($direccionSucursal) ?></p>
            <?php endif; ?>
            <?php if (!empty($correoEmpresa)): ?>
                <p style="margin: 0;"><strong>Correo:</strong> <?= htmlspecialchars($correoEmpresa) ?></p>
            <?php endif; ?>
        </div>
        <div style="margin-top: 24px; color: #888; font-size: 11px;">
            Sistema de Gestión Escienza &nbsp;|&nbsp; www.escienza.pe
        </div>
        <div style="position: absolute; bottom: 30px; left: 0; width: 100%; text-align: center; color: #888; font-size: 10px;">
            © <?= date('Y') ?> Escienza. Todos los derechos reservados.
        </div>
    </div>

    <!-- RESUMEN DE LOS 3 SOFTWARES -->

    <div style="page-break-after: always;"></div>

    <!-- Tabla SIRE -->
    <h3>Resumen de Series - SIRE</h3>
    <table>
        <thead>
            <tr>
                <th class="text-left">Serie</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Suma Gravada</th>
                <th class="text-right">Suma Exonerada</th>
                <th class="text-right">Suma Inafecta</th>
                <th class="text-right">Suma IGV</th>
                <th class="text-right">Suma Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cuadresSIRE)): ?>
                <?php foreach ($cuadresSIRE as $i => $cuadre): ?>
                    <tr class="<?= $cuadre['monto_total'] < 0 ? 'bg-red' : '' ?>">
                        <td class="text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                        <td class="text-right"><?= $cuadre['cantidad_compr'] ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                        <td class="text-right font-bold"><?= number_format($cuadre['monto_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No hay cuadres SIRE para este mes.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Tabla NUBOX -->
    <h3>Resumen de Series - NUBOX360</h3>
    <table>
        <thead>
            <tr>
                <th class="text-left">Serie</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Suma Gravada</th>
                <th class="text-right">Suma Exonerada</th>
                <th class="text-right">Suma Inafecta</th>
                <th class="text-right">Suma IGV</th>
                <th class="text-right">Suma Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cuadresNUBOX)): ?>
                <?php foreach ($cuadresNUBOX as $i => $cuadre): ?>
                    <tr class="<?= $cuadre['monto_total'] < 0 ? 'bg-red' : '' ?>">
                        <td class="text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                        <td class="text-right"><?= $cuadre['cantidad_compr'] ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                        <td class="text-right font-bold"><?= number_format($cuadre['monto_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No hay cuadres NUBOX360 para este mes.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Tabla EDSUITE -->
    <h3>Resumen de Series - EDSUITE</h3>
    <table>
        <thead>
            <tr>
                <th class="text-left">Serie</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Suma Gravada</th>
                <th class="text-right">Suma Exonerada</th>
                <th class="text-right">Suma Inafecta</th>
                <th class="text-right">Suma IGV</th>
                <th class="text-right">Suma Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cuadresEDSUITE)): ?>
                <?php foreach ($cuadresEDSUITE as $i => $cuadre): ?>
                    <tr class="<?= $cuadre['monto_total'] < 0 ? 'bg-red' : '' ?>">
                        <td class="text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                        <td class="text-right"><?= $cuadre['cantidad_compr'] ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                        <td class="text-right"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                        <td class="text-right font-bold"><?= number_format($cuadre['monto_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No hay cuadres EDSUITE para este mes.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- RESUMEN DE COMPROBANTES Y DIFERENCIAS -->

    <div style="page-break-after: always;"></div>

    <!-- RESUMEN FACTURAS Y BOLETAS -->

    <h3>DIFERENCIAS (SIRE - NUBOX)</h3>

    <table>
        <thead>
            <tr>
                <th colspan="2" class="bg-header font-bold">FACTURAS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>SIRE</td>
                <td class="text-right"><?= isset($totalesTipoDoc['FACTURA'][2]) ? 'S/ ' . number_format($totalesTipoDoc['FACTURA'][2], 2) : 'S/ 0.00' ?></td>
            </tr>
            <tr>
                <td>NUBOX</td>
                <td class="text-right"><?= isset($totalesTipoDoc['FACTURA'][1]) ? 'S/ ' . number_format($totalesTipoDoc['FACTURA'][1], 2) : 'S/ 0.00' ?></td>
            </tr>
            <?php
            $faltanteFact = (isset($totalesTipoDoc['FACTURA'][2]) ? $totalesTipoDoc['FACTURA'][2] : 0) - (isset($totalesTipoDoc['FACTURA'][1]) ? $totalesTipoDoc['FACTURA'][1] : 0);
            $faltanteClass = ($faltanteFact != 0) ? 'bg-red' : '';
            ?>
            <tr class="font-bold <?= $faltanteClass ?> bg-total">
                <td>FALTANTE</td>
                <td class="text-right">S/ <?= number_format($faltanteFact, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="2" class="bg-header font-bold">BOLETAS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>SIRE</td>
                <td class="text-right"><?= isset($totalesTipoDoc['BOLETA'][2]) ? 'S/ ' . number_format($totalesTipoDoc['BOLETA'][2], 2) : 'S/ 0.00' ?></td>
            </tr>
            <tr>
                <td>NUBOX</td>
                <td class="text-right"><?= isset($totalesTipoDoc['BOLETA'][1]) ? 'S/ ' . number_format($totalesTipoDoc['BOLETA'][1], 2) : 'S/ 0.00' ?></td>
            </tr>
            <?php
            $faltanteBoleta = (isset($totalesTipoDoc['BOLETA'][2]) ? $totalesTipoDoc['BOLETA'][2] : 0) - (isset($totalesTipoDoc['BOLETA'][1]) ? $totalesTipoDoc['BOLETA'][1] : 0);
            $faltanteClassB = ($faltanteBoleta != 0) ? 'bg-red' : '';
            ?>
            <tr class="font-bold <?= $faltanteClassB ?> bg-total">
                <td>FALTANTE</td>
                <td class="text-right">S/ <?= number_format($faltanteBoleta, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- DIFERENCIAS -->

    <table>
        <thead>
            <tr>
                <th class="text-left">SERIE</th>
                <th class="text-right">TOTAL SIRE</th>
                <th class="text-right">TOTAL NUBOX</th>
                <th class="text-right">DIFERENCIA R.G</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalSire = 0;
            $totalNubox = 0;
            $totalDif = 0;
            foreach ($diferenciasSeries as $row):
                $totalSire += $row['total_sire'];
                $totalNubox += $row['total_nubox'];
                $totalDif += $row['diferencia'];
            ?>
                <tr>
                    <td class="text-left"><?= htmlspecialchars($row['serie']) ?></td>
                    <td class="text-right"><?= number_format($row['total_sire'], 2) ?></td>
                    <td class="text-right"><?= number_format($row['total_nubox'], 2) ?></td>
                    <td class="text-right"><?= number_format($row['diferencia'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="bg-total">
                <td>TOTAL</td>
                <td class="text-right"><?= number_format($totalSire, 2) ?></td>
                <td class="text-right"><?= number_format($totalNubox, 2) ?></td>
                <td class="text-right"><?= number_format($totalDif, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <footer>
        © <?= date('Y') ?> Escienza. Todos los derechos reservados.
    </footer>
</body>

</html>