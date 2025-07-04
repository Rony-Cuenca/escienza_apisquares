<?php
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain', 'Spanish');
date_default_timezone_set('America/Lima');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Cuadres</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            background: white;
            color: #333;
            line-height: 1.4;
        }

        h1 {
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
            font-size: 18px;
            color: #1f2937;
            border-bottom: 3px solid #2563EB;
            padding-bottom: 10px;
        }

        h2 {
            text-align: center;
            margin: 15px 0;
            text-transform: uppercase;
            font-size: 16px;
            color: #1f2937;
        }

        h3 {
            margin: 20px 0 8px 0;
            color: #1f2937;
            font-size: 14px;
            font-weight: bold;
            background: linear-gradient(90deg, #2563EB, #60a5fa);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
        }

        .section-title {
            background: #f8fafc;
            border-left: 4px solid #2563EB;
            padding: 10px 15px;
            margin: 20px 0 10px 0;
            font-weight: bold;
            font-size: 13px;
            color: #1f2937;
        }

        .executive-summary {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 2px solid #2563EB;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .kpi-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .kpi-card.success {
            border-color: #10b981;
            background: #ecfdf5 !important;
            color: #065f46 !important;
        }

        .kpi-card.warning {
            border-color: #f59e0b;
            background: #fffbeb !important;
            color: #92400e !important;
        }

        .kpi-card.danger {
            border-color: #ef4444;
            background: #fef2f2 !important;
            color: #991b1b !important;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }

        .kpi-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .table-modern {
            background: white;
        }

        .table-header {
            background: #1f2937 !important;
            color: #ffffff !important;
            font-weight: bold !important;
        }

        .table-header th {
            background: #1f2937 !important;
            color: #ffffff !important;
            font-weight: bold !important;
            padding: 10px 12px !important;
            text-align: center !important;
            border: 1px solid #374151 !important;
        }

        .table-subheader {
            background: #374151 !important;
            color: #ffffff !important;
            font-weight: bold !important;
        }

        .table-subheader th {
            background: #374151 !important;
            color: #ffffff !important;
            font-weight: bold !important;
            padding: 10px 12px !important;
            text-align: center !important;
            border: 1px solid #4b5563 !important;
        }

        th,
        td {
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Estados y colores */
        .bg-success {
            background: #10b981 !important;
            color: #ffffff !important;
            font-weight: bold !important;
        }

        .bg-danger {
            background: #ef4444 !important;
            color: #ffffff !important;
            font-weight: bold !important;
        }

        .bg-warning {
            background: #f59e0b !important;
            color: #000000 !important;
            font-weight: bold !important;
        }

        .bg-total {
            background: #374151 !important;
            color: #ffffff !important;
            font-weight: bold !important;
        }

        .bg-credit-note {
            background: #ef4444 !important;
            color: #ffffff !important;
            font-weight: bold !important;
        }

        .row-hover:hover {
            background-color: #f8fafc;
        }

        /* Diseño de portada */
        .cover-page {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        .cover-title {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .cover-info {
            background: white;
            border-radius: 15px;
            padding: 30px 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 2px solid #2563EB;
            min-width: 400px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px dotted #e5e7eb;
        }

        .info-label {
            font-weight: 600;
            color: #374151;
        }

        .info-value {
            color: #1f2937;
        }

        /* Headers corporativos */
        .corporate-header {
            background: #1f2937 !important;
            color: #ffffff !important;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .corporate-header h3 {
            margin: 0;
            font-size: 16px;
            background: none !important;
            color: #ffffff !important;
            padding: 0;
        }

        /* Comparativa lado a lado */
        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }

        .comparison-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #e5e7eb;
        }

        .comparison-header {
            background: #1f2937 !important;
            color: #ffffff !important;
            padding: 12px;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
        }

        .comparison-content {
            padding: 0;
        }

        .comparison-content table {
            margin: 0;
            box-shadow: none;
        }

        /* Estilos para diferencias */
        .difference-highlight {
            position: relative;
        }

        .difference-highlight::after {
            content: "!";
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            color: #ef4444;
            font-weight: bold;
        }

        /* Footer corporativo */
        footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background: #1f2937 !important;
            color: #ffffff !important;
            font-size: 9px;
            text-align: center;
            padding: 8px 0;
            border-top: 2px solid #2563EB;
        }

        /* Páginas */
        @page {
            margin: 15mm;
            margin-bottom: 20mm;
        }

        .page-break {
            page-break-after: always;
        }

        /* Responsive adjustments for PDF */
        @media print {
            .kpi-grid {
                display: block;
            }

            .kpi-card {
                display: inline-block;
                width: 30%;
                margin: 1%;
                vertical-align: top;
            }

            .comparison-grid {
                display: block;
            }

            .comparison-card {
                display: inline-block;
                width: 32%;
                margin: 0.5%;
                vertical-align: top;
            }
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <table style="width:100%; margin-bottom: 15px; border: none;">
        <tr>
            <td style="width: 120px; border: none; vertical-align: middle;">
                <img src="http://localhost/escienza_apisquares/public/img/logo.png" alt="Logo" style="width: 60px; margin: 0;">
            </td>
            <td style="text-align: right; border: none; vertical-align: middle; font-size: 11px; color: #2563EB; font-weight: 600;">
                Sistema de Gestión Escienza | www.escienza.pe
            </td>
        </tr>
    </table>

    <!-- PORTADA -->
    <div class="cover-page">
        <div class="cover-title">
            REPORTE MENSUAL<br>
            <span style="font-size: 20px; color: #2563EB;"><?= strtoupper(htmlspecialchars($nombreMes)) ?></span>
        </div>

        <div class="cover-info">
            <div class="info-row">
                <span class="info-label">Período:</span>
                <span class="info-value"><?= htmlspecialchars($nombreMes) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Generado por:</span>
                <span class="info-value"><?= htmlspecialchars($usuarioNombre) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">RUC:</span>
                <span class="info-value"><?= htmlspecialchars($rucEstablecimiento) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Razón Social:</span>
                <span class="info-value"><?= htmlspecialchars($nombreEstablecimiento) ?></span>
            </div>
            <?php if (!empty($direccionEstablecimiento)): ?>
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value"><?= htmlspecialchars($direccionEstablecimiento) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($correoEmpresa)): ?>
                <div class="info-row">
                    <span class="info-label">Correo:</span>
                    <span class="info-value"><?= htmlspecialchars($correoEmpresa) ?></span>
                </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Fecha de generación:</span>
                <span class="info-value"><?= date('d/m/Y H:i:s') ?></span>
            </div>
        </div>
    </div>

    <!-- PÁGINA 2: ANÁLISIS DE DIFERENCIAS -->
    <div class="page-break"></div>

    <div class="corporate-header">
        <h3>ANÁLISIS DE DIFERENCIAS - SIRE vs NUBOX360</h3>
    </div>

    <!-- Grid de comparación por tipo de documento -->
    <div class="comparison-grid">
        <!-- FACTURAS -->
        <div class="comparison-card">
            <div class="comparison-header">
                FACTURAS
            </div>
            <div class="comparison-content">
                <table class="table-modern">
                    <tbody>
                        <tr>
                            <td class="font-bold">SIRE</td>
                            <td class="text-right"><?= isset($totalesTipoDoc[2][2]) ? 'S/ ' . number_format($totalesTipoDoc[2][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold">NUBOX360</td>
                            <td class="text-right"><?= isset($totalesTipoDoc[2][1]) ? 'S/ ' . number_format($totalesTipoDoc[2][1], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <?php
                        $faltanteFact = (isset($totalesTipoDoc[2][2]) ? $totalesTipoDoc[2][2] : 0) - (isset($totalesTipoDoc[2][1]) ? $totalesTipoDoc[2][1] : 0);
                        $faltanteClass = ($faltanteFact == 0) ? 'bg-success' : 'bg-danger';
                        ?>
                        <tr class="<?= $faltanteClass ?>">
                            <td class="font-bold">DIFERENCIA</td>
                            <td class="text-right font-bold">S/ <?= number_format($faltanteFact, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BOLETAS -->
        <div class="comparison-card">
            <div class="comparison-header">
                BOLETAS
            </div>
            <div class="comparison-content">
                <table class="table-modern">
                    <tbody>
                        <tr>
                            <td class="font-bold">SIRE</td>
                            <td class="text-right"><?= isset($totalesTipoDoc[1][2]) ? 'S/ ' . number_format($totalesTipoDoc[1][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold">NUBOX360</td>
                            <td class="text-right"><?= isset($totalesTipoDoc[1][1]) ? 'S/ ' . number_format($totalesTipoDoc[1][1], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <?php
                        $faltanteBoleta = (isset($totalesTipoDoc[1][2]) ? $totalesTipoDoc[1][2] : 0) - (isset($totalesTipoDoc[1][1]) ? $totalesTipoDoc[1][1] : 0);
                        $faltanteClassB = ($faltanteBoleta == 0) ? 'bg-success' : 'bg-danger';
                        ?>
                        <tr class="<?= $faltanteClassB ?>">
                            <td class="font-bold">DIFERENCIA</td>
                            <td class="text-right font-bold">S/ <?= number_format($faltanteBoleta, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- NOTAS DE CRÉDITO -->
        <div class="comparison-card">
            <div class="comparison-header">
                NOTAS DE CRÉDITO
            </div>
            <div class="comparison-content">
                <table class="table-modern">
                    <tbody>
                        <tr>
                            <td class="font-bold">SIRE</td>
                            <td class="text-right"><?= isset($totalesTipoDoc[3][2]) ? 'S/ ' . number_format($totalesTipoDoc[3][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold">NUBOX360</td>
                            <td class="text-right"><?= isset($totalesTipoDoc[3][1]) ? 'S/ ' . number_format($totalesTipoDoc[3][1], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <?php
                        $faltanteNC = (isset($totalesTipoDoc[3][2]) ? $totalesTipoDoc[3][2] : 0) - (isset($totalesTipoDoc[3][1]) ? $totalesTipoDoc[3][1] : 0);
                        $faltanteClassNC = ($faltanteNC == 0) ? 'bg-success' : 'bg-warning';
                        ?>
                        <tr class="<?= $faltanteClassNC ?>">
                            <td class="font-bold">DIFERENCIA</td>
                            <td class="text-right font-bold">S/ <?= number_format($faltanteNC, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- DETALLE DE DIFERENCIAS POR SERIE -->
    <div class="section-title">
        ANÁLISIS DETALLADO POR SERIE
    </div>

    <table class="table-modern">
        <thead class="table-header">
            <tr>
                <th class="text-left">SERIE</th>
                <th class="text-right">TOTAL SIRE</th>
                <th class="text-right">TOTAL NUBOX360</th>
                <th class="text-right">DIFERENCIA</th>
                <th class="text-center">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalSire = 0;
            $totalNubox = 0;
            $totalDif = 0;
            $seriesConDiferencias = 0;
            foreach ($diferenciasSeries as $row):
                $totalSire += $row['total_sire'];
                $totalNubox += $row['total_nubox'];
                $totalDif += $row['diferencia'];
                $tieneDiferencia = $row['diferencia'] != 0;
                if ($tieneDiferencia) $seriesConDiferencias++;
                $rowClass = $tieneDiferencia ? 'bg-danger' : 'row-hover';
            ?>
                <tr class="<?= $rowClass ?>">
                    <td class="text-left font-bold"><?= htmlspecialchars($row['serie']) ?></td>
                    <td class="text-right">S/ <?= number_format($row['total_sire'], 2) ?></td>
                    <td class="text-right">S/ <?= number_format($row['total_nubox'], 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($row['diferencia'], 2) ?></td>
                    <td class="text-center">
                        <?= $tieneDiferencia ? 'REVISAR' : 'OK' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="bg-total">
                <td class="font-bold">TOTALES GENERALES</td>
                <td class="text-right font-bold">S/ <?= number_format($totalSire, 2) ?></td>
                <td class="text-right font-bold">S/ <?= number_format($totalNubox, 2) ?></td>
                <td class="text-right font-bold">S/ <?= number_format($totalDif, 2) ?></td>
                <td class="text-center font-bold">
                    <?= $totalDif == 0 ? 'CUADRADO' : 'DESCUADRE' ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- PÁGINA 3: DETALLE POR SISTEMA -->
    <div class="page-break"></div>

    <div class="corporate-header">
        <h3>DETALLE COMPLETO POR SISTEMA DE FACTURACIÓN</h3>
    </div>

    <!-- SIRE -->
    <div class="section-title">
        SISTEMA SIRE - SUNAT
    </div>
    <table class="table-modern">
        <thead class="table-header">
            <tr>
                <th class="text-left">Serie</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Suma Gravada</th>
                <th class="text-right">Suma Exonerada</th>
                <th class="text-right">Suma Inafecta</th>
                <th class="text-right">Suma IGV</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cuadresSIRE)): ?>
                <?php
                $totalCantidadSIRE = 0;
                $totalGravadaSIRE = 0;
                $totalExoneradaSIRE = 0;
                $totalInafectaSIRE = 0;
                $totalIgvSIRE = 0;
                $totalMontoSIRE = 0;
                foreach ($cuadresSIRE as $i => $cuadre):
                    $totalCantidadSIRE += $cuadre['cantidad_compr'];
                    $totalGravadaSIRE += $cuadre['suma_gravada'];
                    $totalExoneradaSIRE += $cuadre['suma_exonerada'];
                    $totalInafectaSIRE += $cuadre['suma_inafecto'];
                    $totalIgvSIRE += $cuadre['suma_igv'];
                    $totalMontoSIRE += $cuadre['monto_total'];
                    $esNotaCredito = $cuadre['tipo_comprobante'] == 3;
                ?>
                    <tr class="<?= $esNotaCredito ? 'bg-credit-note' : 'row-hover' ?>">
                        <td class="text-left font-bold">
                            <?= htmlspecialchars($cuadre['serie']) ?>
                            <?= $esNotaCredito ? ' (NC)' : '' ?>
                        </td>
                        <td class="text-right"><?= number_format($cuadre['cantidad_compr'], 0) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_gravada'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_igv'], 2) ?></td>
                        <td class="text-right font-bold">S/ <?= number_format($cuadre['monto_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bg-total">
                    <td class="text-left font-bold">TOTAL SIRE</td>
                    <td class="text-right font-bold"><?= number_format($totalCantidadSIRE, 0) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalGravadaSIRE, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalExoneradaSIRE, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalInafectaSIRE, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalIgvSIRE, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalMontoSIRE, 2) ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #6b7280;">
                        No hay registros SIRE para este período
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- NUBOX360 -->
    <div class="section-title">
        SISTEMA NUBOX360
    </div>
    <table class="table-modern">
        <thead class="table-header">
            <tr>
                <th class="text-left">Serie</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Suma Gravada</th>
                <th class="text-right">Suma Exonerada</th>
                <th class="text-right">Suma Inafecta</th>
                <th class="text-right">Suma IGV</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cuadresNUBOX)): ?>
                <?php
                $totalCantidadNUBOX = 0;
                $totalGravadaNUBOX = 0;
                $totalExoneradaNUBOX = 0;
                $totalInafectaNUBOX = 0;
                $totalIgvNUBOX = 0;
                $totalMontoNUBOX = 0;
                foreach ($cuadresNUBOX as $i => $cuadre):
                    $totalCantidadNUBOX += $cuadre['cantidad_compr'];
                    $totalGravadaNUBOX += $cuadre['suma_gravada'];
                    $totalExoneradaNUBOX += $cuadre['suma_exonerada'];
                    $totalInafectaNUBOX += $cuadre['suma_inafecto'];
                    $totalIgvNUBOX += $cuadre['suma_igv'];
                    $totalMontoNUBOX += $cuadre['monto_total'];
                    $esNotaCredito = $cuadre['tipo_comprobante'] == 3;
                ?>
                    <tr class="<?= $esNotaCredito ? 'bg-credit-note' : 'row-hover' ?>">
                        <td class="text-left font-bold">
                            <?= htmlspecialchars($cuadre['serie']) ?>
                            <?= $esNotaCredito ? ' (NC)' : '' ?>
                        </td>
                        <td class="text-right"><?= number_format($cuadre['cantidad_compr'], 0) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_gravada'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_igv'], 2) ?></td>
                        <td class="text-right font-bold">S/ <?= number_format($cuadre['monto_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bg-total">
                    <td class="text-left font-bold">TOTAL NUBOX360</td>
                    <td class="text-right font-bold"><?= number_format($totalCantidadNUBOX, 0) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalGravadaNUBOX, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalExoneradaNUBOX, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalInafectaNUBOX, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalIgvNUBOX, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalMontoNUBOX, 2) ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #6b7280;">
                        No hay registros NUBOX360 para este período
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- EDSUITE -->
    <div class="section-title" style="margin-top: 30px;">
        SISTEMA EDSUITE
    </div>
    <table class="table-modern">
        <thead class="table-header">
            <tr>
                <th class="text-left">Serie</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Suma Gravada</th>
                <th class="text-right">Suma Exonerada</th>
                <th class="text-right">Suma Inafecta</th>
                <th class="text-right">Suma IGV</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cuadresEDSUITE)): ?>
                <?php
                $totalCantidadEDSUITE = 0;
                $totalGravadaEDSUITE = 0;
                $totalExoneradaEDSUITE = 0;
                $totalInafectaEDSUITE = 0;
                $totalIgvEDSUITE = 0;
                $totalMontoEDSUITE = 0;
                foreach ($cuadresEDSUITE as $i => $cuadre):
                    $totalCantidadEDSUITE += $cuadre['cantidad_compr'];
                    $totalGravadaEDSUITE += $cuadre['suma_gravada'];
                    $totalExoneradaEDSUITE += $cuadre['suma_exonerada'];
                    $totalInafectaEDSUITE += $cuadre['suma_inafecto'];
                    $totalIgvEDSUITE += $cuadre['suma_igv'];
                    $totalMontoEDSUITE += $cuadre['monto_total'];
                    $esNotaCredito = $cuadre['tipo_comprobante'] == 3;
                ?>
                    <tr class="<?= $esNotaCredito ? 'bg-credit-note' : 'row-hover' ?>">
                        <td class="text-left font-bold">
                            <?= htmlspecialchars($cuadre['serie']) ?>
                            <?= $esNotaCredito ? ' (NC)' : '' ?>
                        </td>
                        <td class="text-right"><?= number_format($cuadre['cantidad_compr'], 0) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_gravada'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($cuadre['suma_igv'], 2) ?></td>
                        <td class="text-right font-bold">S/ <?= number_format($cuadre['monto_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bg-total">
                    <td class="text-left font-bold">TOTAL EDSUITE</td>
                    <td class="text-right font-bold"><?= number_format($totalCantidadEDSUITE, 0) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalGravadaEDSUITE, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalExoneradaEDSUITE, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalInafectaEDSUITE, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalIgvEDSUITE, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalMontoEDSUITE, 2) ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #6b7280;">
                        No hay registros EDSUITE para este período
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- PÁGINA 4: INFORMACIÓN ADICIONAL -->
    <div class="page-break"></div>

    <!-- SERIES AJENAS -->
    <?php if (!empty($seriesAjenas)): ?>
        <div class="section-title">
            SERIES AJENAS (EXCLUIDAS DEL ANÁLISIS PRINCIPAL)
        </div>
        <table class="table-modern">
            <thead class="table-subheader">
                <tr>
                    <th class="text-left">Serie</th>
                    <th class="text-right">Conteo Total</th>
                    <th class="text-right">Importe Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalConteo = 0;
                $totalImporte = 0;
                foreach ($seriesAjenas as $serie):
                    $totalConteo += $serie['total_conteo'];
                    $totalImporte += $serie['total_importe'];
                ?>
                    <tr class="row-hover">
                        <td class="text-left font-bold"><?= htmlspecialchars($serie['serie']) ?></td>
                        <td class="text-right"><?= number_format($serie['total_conteo'], 0) ?></td>
                        <td class="text-right">S/ <?= number_format($serie['total_importe'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bg-warning">
                    <td class="text-left font-bold">TOTAL SERIES AJENAS</td>
                    <td class="text-right font-bold"><?= number_format($totalConteo, 0) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalImporte, 2) ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- VENTAS GLOBALES -->
    <?php if (!empty($ventasGlobales)): ?>
        <div class="section-title">
            RESUMEN DE VENTAS GLOBALES POR PRODUCTO
        </div>
        <table class="table-modern">
            <thead class="table-subheader">
                <tr>
                    <th class="text-left">Producto</th>
                    <th class="text-right">Cantidad Total</th>
                    <th class="text-right">Importe Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalCantidadVG = 0;
                $totalImporteVG = 0;
                foreach ($ventasGlobales as $venta):
                    $totalCantidadVG += $venta['total_cantidad'];
                    $totalImporteVG += $venta['total_importe'];
                ?>
                    <tr class="row-hover">
                        <td class="text-left font-bold"><?= htmlspecialchars($venta['producto']) ?></td>
                        <td class="text-right"><?= number_format($venta['total_cantidad'], 2) ?></td>
                        <td class="text-right">S/ <?= number_format($venta['total_importe'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bg-total">
                    <td class="text-left font-bold">TOTAL VENTAS GLOBALES</td>
                    <td class="text-right font-bold"><?= number_format($totalCantidadVG, 2) ?></td>
                    <td class="text-right font-bold">S/ <?= number_format($totalImporteVG, 2) ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- RESUMEN EJECUTIVO CON KPIs -->
    <div class="page-break"></div>
    <div class="executive-summary">
        <h2 style="margin: 0 0 15px 0; color: #1f2937;">RESUMEN EJECUTIVO</h2>
        <div class="kpi-grid">
            <div class="kpi-card <?= (isset($totalesTipoDoc[2]) && ($totalesTipoDoc[2][2] ?? 0) - ($totalesTipoDoc[2][1] ?? 0) == 0) ? 'success' : 'danger' ?>">
                <div class="kpi-label">FACTURAS</div>
                <div class="kpi-value">
                    S/ <?= isset($totalesTipoDoc[2]) ? number_format(abs(($totalesTipoDoc[2][2] ?? 0) - ($totalesTipoDoc[2][1] ?? 0)), 2) : '0.00' ?>
                </div>
                <div style="font-size: 9px;">
                    <?= (isset($totalesTipoDoc[2]) && ($totalesTipoDoc[2][2] ?? 0) - ($totalesTipoDoc[2][1] ?? 0) == 0) ? 'SIN DIFERENCIAS' : 'CON DIFERENCIAS' ?>
                </div>
            </div>

            <div class="kpi-card <?= (isset($totalesTipoDoc[1]) && ($totalesTipoDoc[1][2] ?? 0) - ($totalesTipoDoc[1][1] ?? 0) == 0) ? 'success' : 'danger' ?>">
                <div class="kpi-label">BOLETAS</div>
                <div class="kpi-value">
                    S/ <?= isset($totalesTipoDoc[1]) ? number_format(abs(($totalesTipoDoc[1][2] ?? 0) - ($totalesTipoDoc[1][1] ?? 0)), 2) : '0.00' ?>
                </div>
                <div style="font-size: 9px;">
                    <?= (isset($totalesTipoDoc[1]) && ($totalesTipoDoc[1][2] ?? 0) - ($totalesTipoDoc[1][1] ?? 0) == 0) ? 'SIN DIFERENCIAS' : 'CON DIFERENCIAS' ?>
                </div>
            </div>

            <div class="kpi-card <?= (isset($totalesTipoDoc[3]) && ($totalesTipoDoc[3][2] ?? 0) - ($totalesTipoDoc[3][1] ?? 0) == 0) ? 'success' : 'warning' ?>">
                <div class="kpi-label">NOTAS DE CRÉDITO</div>
                <div class="kpi-value">
                    S/ <?= isset($totalesTipoDoc[3]) ? number_format(abs(($totalesTipoDoc[3][2] ?? 0) - ($totalesTipoDoc[3][1] ?? 0)), 2) : '0.00' ?>
                </div>
                <div style="font-size: 9px;">
                    <?= (isset($totalesTipoDoc[3]) && ($totalesTipoDoc[3][2] ?? 0) - ($totalesTipoDoc[3][1] ?? 0) == 0) ? 'SIN DIFERENCIAS' : 'CON DIFERENCIAS' ?>
                </div>
            </div>
        </div>
    </div>

    <footer>
        Reporte Mensual | <?= htmlspecialchars($nombreEstablecimiento) ?> | © <?= date('Y') ?> Escienza - Todos los derechos reservados
    </footer>
</body>

</html>