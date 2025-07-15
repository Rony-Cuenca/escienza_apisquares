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
        /* ===================== VARIABLES CSS ===================== */
        :root {
            --color-azul-oscuro: #255C99;
            --color-azul-claro: #DCEDFF;
            --color-amarillo: #F2C14E;
            --color-verde: #3F7D20;
            --color-gris-oscuro: #1f2937;
            --color-gris-medio: #374151;
            --color-gris-borde: #e5e7eb;
            --color-borde-card: #B5C9E2;
            --color-blanco: #fff;
            --color-negro: #222;
            --border-radius-card: 8px;
            --border-radius-header: 6px;
            --padding-card: 8px 4px 12px 4px;
            --padding-header: 15px 20px;
            --padding-footer: 8px 0;
            --font-size-base: 10px;
            --font-size-header: 13px;
            --font-size-title: 28px;
            --font-size-footer: 9px;
            --font-size-th: 11px;
            --font-size-td: 10px;
        }

        /* ===================== ESTILOS GENERALES ===================== */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: var(--font-size-base);
            background: var(--color-blanco);
            color: #333;
            line-height: 1.4;
        }

        .cover-page {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        .cover-title {
            font-size: var(--font-size-title);
            font-weight: bold;
            color: var(--color-gris-oscuro);
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .cover-info {
            background: var(--color-blanco);
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
            border-bottom: 1px dotted var(--color-gris-borde);
        }

        .info-label {
            font-weight: 600;
            color: var(--color-gris-medio);
        }

        .info-value {
            color: var(--color-gris-oscuro);
        }

        .corporate-header {
            background: var(--color-gris-oscuro) !important;
            color: var(--color-blanco) !important;
            padding: var(--padding-header);
            margin-bottom: 20px;
            border-radius: var(--border-radius-card);
        }

        footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background: var(--color-gris-oscuro) !important;
            color: var(--color-blanco) !important;
            font-size: var(--font-size-footer);
            text-align: center;
            padding: var(--padding-footer);
            border-top: 2px solid #2563EB;
        }

        @page {
            margin: 15mm;
            margin-bottom: 20mm;
        }

        .page-break {
            page-break-after: always;
        }

        .tablas-comparativas-contenedor {
            box-sizing: border-box;
            margin-left: 0;
            padding-left: 0;
        }

        .tablas-comparativas-row-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0;
            margin-bottom: 16px;
            border: none;
            table-layout: fixed;
        }

        .tablas-comparativas-row-table td {
            width: 33.33%;
            vertical-align: top;
            border: none;
            padding-left: 14px;
            padding-right: 14px;
            box-sizing: border-box;
        }

        .tablas-comparativas-row-table td:first-child {
            padding-left: 0 !important;
        }

        .tabla-comparativa {
            background: var(--color-azul-claro);
            border-radius: var(--border-radius-card);
            padding: var(--padding-card);
            border: 1px solid var(--color-borde-card);
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 0;
        }

        .tabla-titulo {
            font-weight: bold;
            color: var(--color-negro);
            text-align: center;
            margin-bottom: 0;
            font-size: var(--font-size-header);
            letter-spacing: 1px;
            background: var(--color-amarillo);
            padding: 5px 0 5px 0;
            border-radius: var(--border-radius-header) var(--border-radius-header) 0 0;
            border-bottom: 2px solid var(--color-amarillo);
        }

        .tabla-comparativa table {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--font-size-td);
            background: var(--color-blanco);
        }

        .tabla-comparativa th,
        .tabla-comparativa td {
            border: 1px solid var(--color-gris-borde);
            padding: 3px 4px;
            text-align: center;
            word-break: break-word;
        }

        /* Utilidades de ancho de columna */
        .col-series {
            width: 52px;
            min-width: 40px;
            max-width: 60px;
        }

        .col-total {
            width: 75px;
            min-width: 55px;
            max-width: 80px;
        }

        .col-nota {
            width: 68px;
            min-width: 45px;
            max-width: 80px;
        }

        .col-diferencia {
            width: 70px;
            min-width: 60px;
            max-width: 90px;
        }

        .tabla-comparativa th {
            background: #255C99 !important;
            color: #fff !important;
            font-weight: 600;
            font-size: var(--font-size-th);
        }

        .nubox-header {
            background: #F2C14E !important;
            color: #222 !important;
            font-weight: bold;
            font-size: var(--font-size-header);
            text-align: center;
            border-radius: var(--border-radius-header) var(--border-radius-header) 0 0;
            border-bottom: 2px solid #F2C14E;
        }

        .nubox-total-row td,
        .nubox-total-label {
            background: #3F7D20 !important;
            color: #fff !important;
            font-weight: bold;
            font-size: var(--font-size-th);
            vertical-align: middle !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .fw-bold {
            font-weight: bold !important;
        }

        @media print {
            .tablas-comparativas-row-table {
                border-spacing: 0 0;
            }

            .tablas-comparativas-row-table td {
                padding-left: 6px;
                padding-right: 6px;
            }

            .tabla-comparativa {
                padding: 4px 2px 6px 2px;
            }

            .tabla-titulo {
                font-size: 11px;
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

    <div>
        <div class="corporate-header">
            <h3>ANÁLISIS COMPARATIVO POR SISTEMA DE FACTURACIÓN</h3>
        </div>
        <div class="tablas-comparativas-contenedor">
            <table class="tablas-comparativas-row-table">
                <tr>
                    <!-- NUBOX -->
                    <td style="vertical-align:top; border:none;">
                        <div class="tabla-comparativa nubox-table">
                            <div class="tabla-titulo nubox-header">NUBOX</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th class="col-series">SERIE</th>
                                        <th class="col-total">TOTAL</th>
                                        <th class="col-nota">NOTA DE CRÉDITO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalNubox = 0;
                                    $totalNotaNubox = 0;
                                    if (!empty($cuadresNUBOX)) {
                                        foreach ($cuadresNUBOX as $cuadre) {
                                            $serie = $cuadre['serie'] ?? '';
                                            $monto = isset($cuadre['monto_total']) ? (float)$cuadre['monto_total'] : 0;
                                            $total = $monto >= 0 ? $monto : 0;
                                            $nota = $monto < 0 ? abs($monto) : 0;
                                            $totalNubox += $total;
                                            $totalNotaNubox += $nota;
                                    ?>
                                            <tr>
                                                <td class="col-series"><?= htmlspecialchars($serie) ?></td>
                                                <td class="col-total text-right">S/. <?= number_format($total, 2) ?></td>
                                                <td class="col-nota text-right">S/. <?= number_format($nota, 2) ?></td>
                                            </tr>
                                    <?php }
                                    }
                                    $totalGeneralNubox = $totalNubox - $totalNotaNubox;
                                    $totalGeneralNuboxStyle = ($totalGeneralNubox < 0) ? 'background:#E54B4B !important; color:#fff; font-weight:bold;' : 'background:#3F7D20 !important; color:#fff !important;';
                                    ?>
                                    <tr class="nubox-total-row">
                                        <td class="nubox-total-label" rowspan="2" style="background:#3F7D20 !important; color:#fff !important;">TOTAL</td>
                                        <td class="col-total text-right" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalNubox, 2) ?></td>
                                        <td class="col-nota text-right" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalNotaNubox, 2) ?></td>
                                    </tr>
                                    <tr class="nubox-total-row">
                                        <td class="text-center fw-bold" colspan="2" style="<?= $totalGeneralNuboxStyle ?>">
                                            S/. <?= number_format($totalGeneralNubox, 2) ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                    <!-- EDSUITE -->
                    <td style="vertical-align:top; border:none;">
                        <div class="tabla-comparativa">
                            <div class="tabla-titulo nubox-header">EDSUITE</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th class="col-series">SERIE</th>
                                        <th class="col-total">TOTAL</th>
                                        <th class="col-nota">NOTA DE CRÉDITO</th>
                                        <th class="col-diferencia">DIFERENCIA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalEdsuite = 0;
                                    $totalNotaEdsuite = 0;
                                    $totalDiferenciaEdsuite = 0;
                                    $nuboxPorSerie = [];
                                    if (!empty($cuadresNUBOX)) {
                                        foreach ($cuadresNUBOX as $c) {
                                            $nuboxPorSerie[$c['serie']] = isset($c['monto_total']) ? (float)$c['monto_total'] : 0;
                                        }
                                    }
                                    if (!empty($cuadresEDSUITE)) {
                                        foreach ($cuadresEDSUITE as $cuadre) {
                                            $serie = $cuadre['serie'] ?? '';
                                            $monto = isset($cuadre['monto_total']) ? (float)$cuadre['monto_total'] : 0;
                                            $total = $monto >= 0 ? $monto : 0;
                                            $nota = $monto < 0 ? abs($monto) : 0;
                                            $totalEdsuite += $total;
                                            $totalNotaEdsuite += $nota;
                                            $diferencia = ($total + $nota) - ($nuboxPorSerie[$serie] ?? 0);
                                            $totalDiferenciaEdsuite += $diferencia;
                                    ?>
                                            <tr>
                                                <td class="col-series"><?= htmlspecialchars($serie) ?></td>
                                                <td class="col-total text-right" <?= $monto < 0 ? ' style="background:#E54B4B !important; color:#fff; font-weight:bold;"' : '' ?>>S/. <?= number_format($total, 2) ?></td>
                                                <td class="col-nota text-right" <?= $nota > 0 ? ' style="background:#E54B4B !important; color:#fff; font-weight:bold;"' : '' ?>>S/. <?= number_format($nota, 2) ?></td>
                                                <td class="col-diferencia text-right" <?= ($diferencia != 0 ? ' style="background:#E54B4B !important; color:#fff; font-weight:bold;"' : '') ?>>S/. <?= number_format($diferencia, 2) ?></td>
                                            </tr>
                                    <?php }
                                    }
                                    ?>
                                    <tr class="nubox-total-row">
                                        <td class="nubox-total-label" rowspan="2" style="background:#3F7D20 !important; color:#fff !important;">TOTAL</td>
                                        <td class="col-total text-right" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalEdsuite, 2) ?></td>
                                        <td class="col-nota text-right" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalNotaEdsuite, 2) ?></td>
                                        <td class="col-diferencia text-right" rowspan="2" style="<?= ($totalDiferenciaEdsuite != 0 ? 'background:#E54B4B !important; color:#fff; font-weight:bold;' : 'background:#3F7D20 !important; color:#fff !important;') ?>">S/. <?= number_format($totalDiferenciaEdsuite, 2) ?></td>
                                    </tr>
                                    <tr class="nubox-total-row">
                                        <td class="text-center fw-bold" colspan="2" style="background:#3F7D20 !important; color:#fff !important;">
                                            S/. <?= number_format($totalEdsuite + $totalNotaEdsuite, 2) ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                    <!-- SIRE -->
                    <td style="vertical-align:top; border:none;">
                        <div class="tabla-comparativa">
                            <div class="tabla-titulo nubox-header">SIRE</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th class="col-series">SERIE</th>
                                        <th class="col-total">TOTAL</th>
                                        <th class="col-nota">NOTA DE CRÉDITO</th>
                                        <th class="col-diferencia">DIFERENCIA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalSire = 0;
                                    $totalNotaSire = 0;
                                    $totalDiferenciaSire = 0;
                                    $nuboxPorSerie = [];
                                    $totalGeneralSire = 0;
                                    if (!empty($cuadresNUBOX)) {
                                        foreach ($cuadresNUBOX as $c) {
                                            $nuboxPorSerie[$c['serie']] = isset($c['monto_total']) ? (float)$c['monto_total'] : 0;
                                        }
                                    }
                                    if (!empty($cuadresSIRE)) {
                                        foreach ($cuadresSIRE as $cuadre) {
                                            $serie = $cuadre['serie'] ?? '';
                                            $monto = isset($cuadre['monto_total']) ? (float)$cuadre['monto_total'] : 0;
                                            $total = $monto >= 0 ? $monto : 0;
                                            $nota = $monto < 0 ? abs($monto) : 0;
                                            $totalSire += $total;
                                            $totalNotaSire += $nota;
                                            $montoNubox = $nuboxPorSerie[$serie] ?? 0;
                                            $montoNuboxTotal = $montoNubox >= 0 ? $montoNubox : 0;
                                            $montoNuboxNota = $montoNubox < 0 ? abs($montoNubox) : 0;
                                            $diferencia = ($total - $nota) - ($montoNuboxTotal - $montoNuboxNota);
                                            $totalDiferenciaSire += $diferencia;
                                    ?>
                                            <tr>
                                                <td class="col-series"><?= htmlspecialchars($serie) ?></td>
                                                <td class="col-total text-right">S/. <?= number_format($total, 2) ?></td>
                                                <td class="col-nota text-right">S/. <?= number_format($nota, 2) ?></td>
                                                <td class="col-diferencia text-right" <?= ($diferencia != 0 ? ' style="background:#E54B4B !important; color:#fff; font-weight:bold;"' : '') ?>>S/. <?= number_format($diferencia, 2) ?></td>
                                            </tr>
                                    <?php }
                                    }
                                    $totalGeneralSire = $totalSire - $totalNotaSire;
                                    $totalDiferenciaSireStyle = ($totalDiferenciaSire != 0) ? 'background:#E54B4B !important; color:#fff; font-weight:bold;' : 'background:#3F7D20 !important; color:#fff !important;';
                                    ?>
                                    <tr class="nubox-total-row">
                                        <td class="nubox-total-label" rowspan="2" style="background:#3F7D20 !important; color:#fff !important;">TOTAL</td>
                                        <td class="col-total text-right" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalSire, 2) ?></td>
                                        <td class="col-nota text-right" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalNotaSire, 2) ?></td>
                                        <td class="col-diferencia text-right" rowspan="2" style="<?= $totalDiferenciaSireStyle ?>">S/. <?= number_format($totalDiferenciaSire, 2) ?></td>
                                    </tr>
                                    <tr class="nubox-total-row">
                                        <td class="text-center fw-bold" colspan="2" style="background:#3F7D20 !important; color:#fff !important;">
                                            S/. <?= number_format($totalGeneralSire, 2) ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="corporate-header">
            <h3>RESUMEN DE COMPROBANTES</h3>
        </div>
        <div class="tablas-comparativas-contenedor">
            <table class="tablas-comparativas-row-table">
                <tr>
                    <!-- FACTURAS -->
                    <td style="vertical-align:top; border:none;">
                        <div class="tabla-comparativa">
                            <div class="tabla-titulo nubox-header">FACTURAS</div>
                            <table>
                                <tbody>
                                    <?php
                                    $facturaSIRE = isset($totalesTipoDoc[2][2]) ? (float)$totalesTipoDoc[2][2] : 0;
                                    $facturaNUBOX = isset($totalesTipoDoc[2][1]) ? (float)$totalesTipoDoc[2][1] : 0;
                                    $faltanteFact = $facturaSIRE - $facturaNUBOX;
                                    ?>
                                    <tr>
                                        <th class="fw-bold" style="background:var(--color-azul-oscuro); color:var(--color-blanco);">SIRE</th>
                                        <td class="text-right">S/. <?= number_format($facturaSIRE, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="fw-bold" style="background:var(--color-azul-oscuro); color:var(--color-blanco);">NUBOX</th>
                                        <td class="text-right">S/. <?= number_format($facturaNUBOX, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="fw-bold" style="background:<?= ($faltanteFact != 0 ? '#E54B4B' : '#3F7D20') ?> !important; color:#fff !important;">FALTANTE</th>
                                        <td class="text-right fw-bold" style="background:<?= ($faltanteFact != 0 ? '#E54B4B' : '#3F7D20') ?> !important; color:#fff !important;">S/. <?= number_format($faltanteFact, 2) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                    <!-- BOLETAS -->
                    <td style="vertical-align:top; border:none;">
                        <div class="tabla-comparativa">
                            <div class="tabla-titulo nubox-header">BOLETAS</div>
                            <table>
                                <tbody>
                                    <?php
                                    $boletaSIRE = isset($totalesTipoDoc[1][2]) ? (float)$totalesTipoDoc[1][2] : 0;
                                    $boletaNUBOX = isset($totalesTipoDoc[1][1]) ? (float)$totalesTipoDoc[1][1] : 0;
                                    $faltanteBoleta = $boletaSIRE - $boletaNUBOX;
                                    ?>
                                    <tr>
                                        <th class="fw-bold" style="background:var(--color-azul-oscuro); color:var(--color-blanco);">SIRE</th>
                                        <td class="text-right">S/. <?= number_format($boletaSIRE, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="fw-bold" style="background:var(--color-azul-oscuro); color:var(--color-blanco);">NUBOX</th>
                                        <td class="text-right">S/. <?= number_format($boletaNUBOX, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="fw-bold" style="background:<?= ($faltanteBoleta != 0 ? '#E54B4B' : '#3F7D20') ?> !important; color:#fff !important;">FALTANTE</th>
                                        <td class="text-right fw-bold" style="background:<?= ($faltanteBoleta != 0 ? '#E54B4B' : '#3F7D20') ?> !important; color:#fff !important;">S/. <?= number_format($faltanteBoleta, 2) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                    <!-- NOTAS DE CRÉDITO -->
                    <td style="vertical-align:top; border:none;">
                        <div class="tabla-comparativa">
                            <div class="tabla-titulo nubox-header">NOTAS DE CRÉDITO</div>
                            <table>
                                <tbody>
                                    <?php
                                    $notaSIRE = isset($totalesTipoDoc[3][2]) ? (float)$totalesTipoDoc[3][2] : 0;
                                    $notaNUBOX = isset($totalesTipoDoc[3][1]) ? (float)$totalesTipoDoc[3][1] : 0;
                                    $faltanteNota = $notaSIRE - $notaNUBOX;
                                    ?>
                                    <tr>
                                        <th class="fw-bold" style="background:var(--color-azul-oscuro); color:var(--color-blanco);">SIRE</th>
                                        <td class="text-right">S/. <?= number_format($notaSIRE, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="fw-bold" style="background:var(--color-azul-oscuro); color:var(--color-blanco);">NUBOX</th>
                                        <td class="text-right">S/. <?= number_format($notaNUBOX, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="fw-bold" style="background:<?= ($faltanteNota != 0 ? '#E54B4B' : '#3F7D20') ?> !important; color:#fff !important;">FALTANTE</th>
                                        <td class="text-right fw-bold" style="background:<?= ($faltanteNota != 0 ? '#E54B4B' : '#3F7D20') ?> !important; color:#fff !important;">S/. <?= number_format($faltanteNota, 2) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- PÁGINA 3: REPORTES GLOBALES Y SERIES AJENAS -->
    <div class="page-break"></div>

    <div>
        <div class="corporate-header">
            <h3>REPORTES GLOBALES Y SERIES AJENAS</h3>
        </div>
        <div class="tablas-comparativas-contenedor">
            <!-- Fila 1: Reportes Globales -->
            <table class="tablas-comparativas-row-table" style="margin-bottom: 25px;">
                <tr>
                    <td style="border:none; text-align:center; vertical-align:top;">
                        <div class="tabla-comparativa" style="display:inline-block; min-width: 600px;">
                            <div class="tabla-titulo nubox-header">REPORTES GLOBALES</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th class="col-series">SERIES</th>
                                        <th class="col-total">COMBUSTIBLES</th>
                                        <th class="col-total">EXTRAS</th>
                                        <th class="col-nota">NOTAS DE CRÉDITO</th>
                                        <th class="col-diferencia">DIFERENCIA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalCombustibles = 0;
                                    $totalExtras = 0;
                                    $totalNotas = 0;
                                    $totalDiferencia = 0;
                                    if (!empty($seriesEdSuite)) {
                                        foreach ($seriesEdSuite as $row) {
                                            $serie = $row['serie'] ?? '';
                                            $combustibles = isset($row['combustible']) ? (float)$row['combustible'] : 0;
                                            $extras = isset($row['extras']) ? (float)$row['extras'] : 0;
                                            $notas = isset($row['nota_credito']) ? (float)$row['nota_credito'] : 0;
                                            $diferencia = isset($row['diferencia']) ? (float)$row['diferencia'] : 0;
                                            $totalCombustibles += $combustibles;
                                            $totalExtras += $extras;
                                            $totalNotas += $notas;
                                            $totalDiferencia += $diferencia;
                                    ?>
                                            <tr>
                                                <td class="col-series"><?= htmlspecialchars($serie) ?></td>
                                                <td class="col-total text-right">S/. <?= number_format($combustibles, 2) ?></td>
                                                <td class="col-total text-right">S/. <?= number_format($extras, 2) ?></td>
                                                <td class="col-nota text-right">S/. <?= number_format($notas, 2) ?></td>
                                                <td class="col-diferencia text-right" <?= ($diferencia != 0 ? ' style="background:#E54B4B !important; color:#fff; font-weight:bold;"' : '') ?>>S/. <?= number_format($diferencia, 2) ?></td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="5" style="text-align:center; color:#888;">No hay datos de reportes globales.</td>
                                        </tr>
                                    <?php } ?>
                                    <tr class="nubox-total-row">
                                        <td class="nubox-total-label" rowspan="2" style="background:#3F7D20 !important; color:#fff !important;">TOTAL</td>
                                        <td class="col-total text-right" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalCombustibles, 2) ?></td>
                                        <td class="col-total text-right" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalExtras, 2) ?></td>
                                        <td class="col-nota text-right" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalNotas, 2) ?></td>
                                        <td class="col-diferencia text-right" style="background:<?= ($totalDiferencia != 0 ? '#E54B4B' : '#3F7D20') ?> !important; color:#fff !important; font-weight:bold;" rowspan="2">S/. <?= number_format($totalDiferencia, 2) ?></td>
                                    </tr>
                                    <tr class="nubox-total-row">
                                        <td class="text-center fw-bold" colspan="3" style="background:#3F7D20 !important; color:#fff !important;">S/. <?= number_format($totalCombustibles + $totalExtras + $totalNotas, 2) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
            <!-- Fila 2: Productos Totale y Series Ajenas-->
            <table class="tablas-comparativas-row-table" style="margin-top:0;">
                <tr>
                    <!-- PRODUCTOS TOTALES -->
                    <td style="vertical-align:top; border:none;">
                        <div class="tabla-comparativa">
                            <div class="tabla-titulo nubox-header">PRODUCTOS TOTALES</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>PRODUCTO</th>
                                        <th>CANTIDAD</th>
                                        <th>MONTO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalCantidad = 0;
                                    $totalImporte = 0;
                                    if (!empty($ventasGlobales)) {
                                        foreach ($ventasGlobales as $venta) {
                                            $producto = $venta['producto'] ?? '';
                                            $cantidad = 0;
                                            if (isset($venta['total_cantidad'])) {
                                                $cantidad = (float)$venta['total_cantidad'];
                                            } elseif (isset($venta['cantidad_total'])) {
                                                $cantidad = (float)$venta['cantidad_total'];
                                            } elseif (isset($venta['cantidad'])) {
                                                $cantidad = (float)$venta['cantidad'];
                                            }
                                            $importe = 0;
                                            if (isset($venta['total_importe'])) {
                                                $importe = (float)$venta['total_importe'];
                                            } elseif (isset($venta['total'])) {
                                                $importe = (float)$venta['total'];
                                            }
                                            $totalCantidad += $cantidad;
                                            $totalImporte += $importe;
                                    ?>
                                            <tr>
                                                <td><?= htmlspecialchars($producto) ?></td>
                                                <td style="text-align:right;"><?= number_format($cantidad, 2) ?></td>
                                                <td style="text-align:right;">S/. <?= number_format($importe, 2) ?></td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="3" style="text-align:center; color:#888;">No hay datos de productos globales.</td>
                                        </tr>
                                    <?php } ?>
                                    <tr class="nubox-total-row">
                                        <td style="font-weight:bold; background:#3F7D20 !important; color:#fff !important;">TOTAL</td>
                                        <td style="text-align:right; background:#3F7D20 !important; color:#fff !important; font-weight:bold;"><?= number_format($totalCantidad, 2) ?></td>
                                        <td style="text-align:right; background:#3F7D20 !important; color:#fff !important; font-weight:bold;">S/. <?= number_format($totalImporte, 2) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                    <!-- SERIES AJENAS -->
                    <td style="vertical-align:top; border:none;">
                        <div class="tabla-comparativa">
                            <div class="tabla-titulo nubox-header">SERIES AJENAS</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>SERIE</th>
                                        <th>CANTIDAD</th>
                                        <th>MONTO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($seriesAjenas)): ?>
                                        <?php
                                        $totalConteo = 0;
                                        $totalImporte = 0;
                                        foreach ($seriesAjenas as $serie):
                                            $totalConteo += $serie['total_conteo'];
                                            $totalImporte += $serie['total_importe'];
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($serie['serie']) ?></td>
                                                <td style="text-align:right;"><?= number_format($serie['total_conteo'], 0) ?></td>
                                                <td style="text-align:right;">S/. <?= number_format($serie['total_importe'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="nubox-total-row">
                                            <td style="font-weight:bold; background:#3F7D20 !important; color:#fff !important;">TOTAL</td>
                                            <td style="text-align:right; background:#3F7D20 !important; color:#fff !important; font-weight:bold;"><?= number_format($totalConteo, 0) ?></td>
                                            <td style="text-align:right; background:#3F7D20 !important; color:#fff !important; font-weight:bold;">S/. <?= number_format($totalImporte, 2) ?></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" style="text-align:center; color:#888;">No hay datos de series ajenas.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <footer>
        Reporte Mensual | <?= htmlspecialchars($nombreEstablecimiento) ?> | © <?= date('Y') ?> Escienza - Todos los derechos reservados
    </footer>
</body>

</html>