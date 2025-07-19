<?php
require_once __DIR__ . '/../../helpers/permisos_helper.php';
$idEstablecimiento = isset($_GET['id_establecimiento']) ? $_GET['id_establecimiento'] : '';
$sinSeries = $idEstablecimiento && empty($cuadresNUBOX) && empty($cuadresEDSUITE) && empty($cuadresSIRE) && empty($seriesEdSuite);
?>
<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="w-full mx-auto bg-white rounded-lg shadow-2xl shadow-gray-300/40 pt-14 px-8 pb-8">
        <!-- Cabecera -->
        <div class="flex flex-col items-center w-full pb-6 border-b border-gray-200 mb-8">
            <h2 class="text-3xl font-bold text-gray-900 text-center uppercase">Reporte de Cuadres</h2>
        </div>

        <!-- Formulario de filtros -->
        <form method="GET" action="index.php" class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
            <input type="hidden" name="controller" value="reporte">
            <input type="hidden" name="action" value="index">
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 flex-1 items-stretch sm:items-end">
                <div class="w-full sm:w-auto">
                    <label class="block text-sm font-medium mb-1">Meses</label>
                    <input
                        type="text"
                        id="mesPicker"
                        name="mes"
                        class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 w-full max-w-[180px] md:max-w-[160px]"
                        placeholder="Seleccione un mes"
                        value="<?= isset($_GET['mes']) ? htmlspecialchars($_GET['mes']) : '' ?>"
                        readonly
                        required
                        onchange="this.form.submit()"
                        autocomplete="off" />
                </div>
                <div class="w-full sm:w-auto">
                    <label class="block text-sm font-medium mb-1">Establecimientos</label>
                    <select name="id_establecimiento" id="establecimientoPicker" class="border rounded px-3 py-2 w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-400 truncate overflow-hidden whitespace-nowrap text-ellipsis" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <?php if (!empty($establecimientos)): ?>
                            <?php $selectedEst = isset($_GET['id_establecimiento']) ? $_GET['id_establecimiento'] : (SesionHelper::obtenerEstablecimientoActual() ?? ''); ?>
                            <?php foreach ($establecimientos as $est): ?>
                                <option value="<?= $est['id'] ?>" <?= ($selectedEst == $est['id']) ? 'selected' : '' ?> class="truncate"><?= htmlspecialchars($est['etiqueta']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <?php if (puedeExportarReportes()): ?>
                <div class="flex gap-2 justify-end">
                    <button
                        type="button"
                        onclick="exportarArchivo('PDF')"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2"
                        <?= $sinSeries ? 'disabled style="opacity:0.5;cursor:allowed;"' : '' ?>>
                        <img src="../src/images/ic_pdf.png" alt="PDF" class="w-5 h-5 bd-w">
                        Exportar PDF
                    </button>
                    <button
                        type="button"
                        onclick="exportarArchivo('Excel')"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2"
                        <?= $sinSeries ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>>
                        <img src="../src/images/ic_excel.png" alt="EXCEL" class="w-5 h-5 bd-w">
                        Exportar Excel
                    </button>
                </div>
            <?php endif; ?>
        </form>

        <?php
        if (empty($_GET['mes'])):
        ?>
            <div class="w-full flex flex-col items-center justify-center py-10">
                <h2 class="text-2xl font-bold text-gray-700 mb-4">Seleccione un mes para ver los reportes</h2>
                <p class="text-gray-500">Utilice el filtro de meses en la parte superior para visualizar los datos.</p>
            </div>
            <?php
        else:
            $idEstablecimiento = isset($_GET['id_establecimiento']) ? $_GET['id_establecimiento'] : '';
            $sinSeries = $idEstablecimiento && empty($cuadresNUBOX) && empty($cuadresEDSUITE) && empty($cuadresSIRE) && empty($seriesEdSuite);
            if ($sinSeries):
            ?>
                <div class="w-full flex flex-col items-center justify-center py-10">
                    <h2 class="text-2xl font-bold text-gray-700 mb-4">No hay reportes para este establecimiento</h2>
                    <p class="text-gray-500">Seleccione otro establecimiento o verifique que tenga series asociadas.</p>
                </div>
            <?php
            else: ?>
                <!-- ANÁLISIS COMPARATIVO POR SISTEMA DE FACTURACIÓN -->
                <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4">ANÁLISIS COMPARATIVO POR SISTEMA DE FACTURACIÓN</h3>
                <div class="flex flex-col md:flex-row gap-5 mb-8 items-start">
                    <!-- Tabla NUBOX360 -->
                    <div class="relative w-full md:w-[300px] flex-shrink-0">
                        <div class="overflow-x-auto bg-white rounded-xl border border-[#2563EB] shadow">
                            <table class="min-w-max w-full text-xs">
                                <thead>
                                    <tr class="bg-yellow-300 text-gray-900 font-bold">
                                        <th class="py-2 px-2 text-center text-sm" colspan="3">NUBOX</th>
                                    </tr>
                                    <tr class="bg-[#A9C3E8]">
                                        <th class="py-2 px-2 text-left">Serie</th>
                                        <th class="py-2 px-2 text-right">Total</th>
                                        <th class="py-2 px-2 text-right">Nota de Crédito</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($cuadresNUBOX)): ?>
                                        <?php
                                        $totalNubox = 0;
                                        $totalNotasNubox = 0;
                                        foreach ($cuadresNUBOX as $cuadre):
                                            $totalNubox += $cuadre['total'];
                                            $totalNotasNubox += $cuadre['nota_credito'];
                                            $isNotaCredito = $cuadre['nota_credito'] < 0;
                                        ?>
                                            <tr class="border-b border-[#2563EB] transition">
                                                <td class="py-2 px-2 text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                                <td class="py-2 px-2 text-right">S/. <?= number_format($cuadre['total'], 2) ?></td>
                                                <td class="py-2 px-2 text-right">S/. <?= number_format($cuadre['nota_credito'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="bg-gray-100 font-bold">
                                            <td class="py-2 px-2 border-r border-[#2563EB] text-center" rowspan="2">TOTAL</td>
                                            <td class="py-2 px-2 text-right border-r-0 border-[#2563EB]">S/. <?= number_format($totalNubox, 2) ?></td>
                                            <td class="py-2 px-2 text-right border-r-0 border-[#2563EB]">S/. <?= number_format($totalNotasNubox, 2) ?></td>
                                        </tr>
                                        <tr class="bg-gray-100 font-bold">
                                            <td class="py-2 px-2 text-center border-t border-[#2563EB]" colspan="2">
                                                S/. <?= number_format($totalNubox + $totalNotasNubox, 2) ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="py-4 text-center text-gray-500">No hay cuadres NUBOX para este mes.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabla EDSUITE -->
                    <div class="relative flex-1 min-w-0">
                        <div class="overflow-x-auto bg-white rounded-xl border border-[#2563EB] shadow">
                            <table class="min-w-max w-full text-xs">
                                <thead>
                                    <tr class="bg-yellow-300 text-gray-900 font-bold">
                                        <th class="py-2 px-2 text-center text-sm" colspan="4">EDSUITE</th>
                                    </tr>
                                    <tr class="bg-[#A9C3E8]">
                                        <th class="py-2 px-2 text-left">Serie</th>
                                        <th class="py-2 px-2 text-right">Total</th>
                                        <th class="py-2 px-2 text-right">Nota de Crédito</th>
                                        <th class="py-2 px-2 text-right">Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($seriesEdSuite)): ?>
                                        <?php
                                        $totalCombustible = 0;
                                        $totalExtras = 0;
                                        $totalNotasEdSuite = 0;
                                        $totalDiferencia = 0;
                                        foreach ($seriesEdSuite as $row):
                                            $totalCombustible += $row['combustible'];
                                            $totalExtras += $row['extras'];
                                            $totalNotasEdSuite += $row['nota_credito'];
                                            $totalDiferencia += $row['diferencia'];
                                        ?>
                                            <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                                <td class="py-2 px-2 text-left"><?= htmlspecialchars($row['serie']) ?></td>
                                                <td class="py-2 px-2 text-right">S/. <?= number_format($row['combustible'], 2) ?></td>
                                                <td class="py-2 px-2 text-right">S/. <?= number_format($row['nota_credito'], 2) ?></td>
                                                <td class="py-2 px-2 text-right<?= ($row['diferencia'] != 0 ? ' bg-red-600 text-white font-bold' : '') ?>">S/. <?= number_format($row['diferencia'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php $totalDiffClass = ($totalDiferencia != 0) ? 'bg-red-600 text-white font-bold' : ''; ?>
                                        <tr class="bg-gray-100 font-bold">
                                            <td class="py-2 px-2 border-r border-[#2563EB] text-center" rowspan="2">TOTAL</td>
                                            <td class="py-2 px-2 text-right border-r-0 border-[#2563EB]">S/. <?= number_format($totalCombustible, 2) ?></td>
                                            <td class="py-2 px-2 text-right border-r border-[#2563EB]">S/. <?= number_format($totalNotasEdSuite, 2) ?></td>
                                            <td class="py-2 px-2 text-right border-r-0 border-[#2563EB] <?= $totalDiffClass ?>" style="<?= $totalDiffClass ? 'background-color:bg-red-600!important;' : '' ?>" rowspan="2">S/. <?= number_format($totalDiferencia, 2) ?></td>
                                        </tr>
                                        <tr class="bg-gray-100 font-bold">
                                            <td class="py-2 px-2 text-center border-t border-r border-[#2563EB]" colspan="2">
                                                S/. <?= number_format($totalCombustible + $totalNotasEdSuite, 2) ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="py-4 text-center text-gray-500">No hay diferencias EDSUITE para este mes.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabla SIRE -->
                    <div class="relative flex-1 min-w-0">
                        <div class="overflow-x-auto bg-white rounded-xl border border-[#2563EB] shadow">
                            <table class="min-w-max w-full text-xs">
                                <thead>
                                    <tr class="bg-yellow-300 text-gray-900 font-bold">
                                        <th class="py-2 px-2 text-center text-sm" colspan="4">SIRE</th>
                                    </tr>
                                    <tr class="bg-[#A9C3E8]">
                                        <th class="py-2 px-2 text-left">Serie</th>
                                        <th class="py-2 px-2 text-right">Total</th>
                                        <th class="py-2 px-2 text-right">Nota de Crédito</th>
                                        <th class="py-2 px-2 text-right">Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($cuadresSIRE)): ?>
                                        <?php
                                        $totalSIRE = 0;
                                        $totalNotasSIRE = 0;
                                        $totalDiferencia = 0;
                                        foreach ($cuadresSIRE as $cuadre):
                                            $serie = $cuadre['serie'];
                                            $totalSIRE += $cuadre['total'];
                                            $totalNotasSIRE += $cuadre['nota_credito'];
                                            $diferencia = isset($diferenciasNuboxSire[$serie]['diferencia']) ? $diferenciasNuboxSire[$serie]['diferencia'] : 0;
                                            $totalDiferencia += $diferencia;
                                        ?>
                                            <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                                <td class="py-2 px-2 text-left"><?= htmlspecialchars($serie) ?></td>
                                                <td class="py-2 px-2 text-right">S/. <?= number_format($cuadre['total'], 2) ?></td>
                                                <td class="py-2 px-2 text-right">S/. <?= number_format($cuadre['nota_credito'], 2) ?></td>
                                                <td class="py-2 px-2 text-right<?= ($diferencia != 0 ? ' bg-red-600 text-white font-bold' : '') ?>">S/. <?= number_format($diferencia, 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php $totalDiffClassSire = ($totalDiferencia != 0) ? 'bg-red-600 text-white font-bold' : ''; ?>
                                        <tr class="bg-gray-100 font-bold">
                                            <td class="py-2 px-2 border-r border-[#2563EB] text-center" rowspan="2">TOTAL</td>
                                            <td class="py-2 px-2 text-right border-r-0 border-[#2563EB]">S/. <?= number_format($totalSIRE, 2) ?></td>
                                            <td class="py-2 px-2 text-right border-r border-[#2563EB]">S/. <?= number_format($totalNotasSIRE, 2) ?></td>
                                            <td class="py-2 px-2 text-right border-r-0 border-[#2563EB] <?= $totalDiffClassSire ?>" style="<?= $totalDiffClassSire ? 'background-color:bg-red-600!important;' : '' ?>" rowspan="2">S/. <?= number_format($totalDiferencia, 2) ?></td>
                                        </tr>
                                        <tr class="bg-gray-100 font-bold">
                                            <td class="py-2 px-2 text-center border-r border-t border-[#2563EB]" colspan="2">
                                                S/. <?= number_format($totalSIRE + $totalNotasSIRE, 2) ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="py-4 text-center text-gray-500">No hay cuadres SIRE para este mes.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RESUMEN FACTURAS, BOLETAS Y NOTAS DE CRÉDITO -->
                <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4">RESUMEN DE COMPROBANTES</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <!-- FACTURAS -->
                    <div class="bg-white rounded-xl border border-[#2563EB] shadow overflow-hidden">
                        <table class="w-full min-w-max text-sm">
                            <thead>
                                <tr class="bg-[#A9C3E8]">
                                    <th colspan="2" class="py-3 px-4 text-left text-lg font-bold border-b border-[#2563EB]">FACTURAS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                    <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">SIRE</td>
                                    <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc[2][2]) ? 'S/ ' . number_format($totalesTipoDoc[2][2], 2) : 'S/ 0.00' ?></td>
                                </tr>
                                <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                    <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">NUBOX</td>
                                    <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc[2][1]) ? 'S/ ' . number_format($totalesTipoDoc[2][1], 2) : 'S/ 0.00' ?></td>
                                </tr>
                                <?php
                                $faltanteFact = (isset($totalesTipoDoc[2][2]) ? $totalesTipoDoc[2][2] : 0) - (isset($totalesTipoDoc[2][1]) ? $totalesTipoDoc[2][1] : 0);
                                $faltanteClass = ($faltanteFact == 0) ? 'bg-[#bbf7d0] text-[#166534]' : 'bg-[#dc3545] text-white';
                                ?>
                                <tr class="font-bold <?= $faltanteClass ?>">
                                    <td class="py-2 px-4 border-t border-[#2563EB]">FALTANTE</td>
                                    <td class="py-2 px-4 text-right border-t border-[#2563EB]">S/ <?= number_format($faltanteFact, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- BOLETAS -->
                    <div class="bg-white rounded-xl border border-[#2563EB] shadow overflow-hidden">
                        <table class="w-full min-w-max text-sm">
                            <thead>
                                <tr class="bg-[#A9C3E8]">
                                    <th colspan="2" class="py-3 px-4 text-left text-lg font-bold border-b border-[#2563EB]">BOLETAS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                    <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">SIRE</td>
                                    <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc[1][2]) ? 'S/ ' . number_format($totalesTipoDoc[1][2], 2) : 'S/ 0.00' ?></td>
                                </tr>
                                <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                    <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">NUBOX</td>
                                    <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc[1][1]) ? 'S/ ' . number_format($totalesTipoDoc[1][1], 2) : 'S/ 0.00' ?></td>
                                </tr>
                                <?php
                                $faltanteBoleta = (isset($totalesTipoDoc[1][2]) ? $totalesTipoDoc[1][2] : 0) - (isset($totalesTipoDoc[1][1]) ? $totalesTipoDoc[1][1] : 0);
                                $faltanteClassB = ($faltanteBoleta == 0) ? 'bg-[#bbf7d0] text-[#166534]' : 'bg-[#dc3545] text-white';
                                ?>
                                <tr class="font-bold <?= $faltanteClassB ?>">
                                    <td class="py-2 px-4 border-t border-[#2563EB]">FALTANTE</td>
                                    <td class="py-2 px-4 text-right border-t border-[#2563EB]">S/ <?= number_format($faltanteBoleta, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- NOTAS DE CRÉDITO -->
                    <div class="bg-white rounded-xl border border-[#2563EB] shadow overflow-hidden">
                        <table class="w-full min-w-max text-sm">
                            <thead>
                                <tr class="bg-[#A9C3E8]">
                                    <th colspan="2" class="py-3 px-4 text-left text-lg font-bold border-b border-[#2563EB]">NOTAS DE CRÉDITO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                    <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">SIRE</td>
                                    <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc[3][2]) ? 'S/ ' . number_format($totalesTipoDoc[3][2], 2) : 'S/ 0.00' ?></td>
                                </tr>
                                <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                    <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">NUBOX</td>
                                    <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc[3][1]) ? 'S/ ' . number_format($totalesTipoDoc[3][1], 2) : 'S/ 0.00' ?></td>
                                </tr>
                                <?php
                                $faltanteNC = (isset($totalesTipoDoc[3][2]) ? $totalesTipoDoc[3][2] : 0) - (isset($totalesTipoDoc[3][1]) ? $totalesTipoDoc[3][1] : 0);
                                $faltanteClassNC = ($faltanteNC == 0) ? 'bg-[#bbf7d0] text-[#166534]' : 'bg-[#dc3545] text-white';
                                ?>
                                <tr class="font-bold <?= $faltanteClassNC ?>">
                                    <td class="py-2 px-4 border-t border-[#2563EB]">FALTANTE</td>
                                    <td class="py-2 px-4 text-right border-t border-[#2563EB]">S/ <?= number_format($faltanteNC, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- REPORTES GLOBALES -->
                <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4">REPORTES GLOBALES</h3>
                <div class="flex flex-col md:flex-row gap-8 mb-8 items-start justify-center">
                    <!-- Tabla PRODUCTOS TOTALES -->
                    <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white">
                        <table class="w-full min-w-max text-sm">
                            <thead>
                                <tr class="bg-yellow-300 text-gray-900 font-bold">
                                    <th class="py-2 px-3 text-center text-sm" colspan="3">PRODUCTOS TOTALES</th>
                                </tr>
                                <tr class="bg-[#A9C3E8]">
                                    <th class="py-2 px-3 text-left">Producto</th>
                                    <th class="py-2 px-3 text-right">Cantidad Total</th>
                                    <th class="py-2 px-3 text-right">Importe Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ventasGlobales)): ?>
                                    <?php
                                    $totalCantidad = 0;
                                    $totalImporteVG = 0;
                                    foreach ($ventasGlobales as $venta):
                                        $totalCantidad += $venta['total_cantidad'];
                                        $totalImporteVG += $venta['total_importe'];
                                    ?>
                                        <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                            <td class="py-2 px-3 text-left font-semibold"><?= htmlspecialchars($venta['producto']) ?></td>
                                            <td class="py-2 px-3 text-right"><?= number_format($venta['total_cantidad'], 2) ?></td>
                                            <td class="py-2 px-3 text-right">S/ <?= number_format($venta['total_importe'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-gray-100 font-bold">
                                        <td class="py-2 px-3">TOTAL</td>
                                        <td class="py-2 px-3 text-right"><?= number_format($totalCantidad, 2) ?></td>
                                        <td class="py-2 px-3 text-right">S/ <?= number_format($totalImporteVG, 2) ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-gray-500">No hay ventas globales para este mes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Tabla REPORTES GLOBALES -->
                    <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white">
                        <table class="w-full min-w-max text-sm">
                            <thead>
                                <tr class="bg-yellow-300 text-gray-900 font-bold">
                                    <th class="py-2 px-3 text-center text-sm" colspan="5">REPORTES GLOBALES</th>
                                </tr>
                                <tr class="bg-[#A9C3E8]">
                                    <th class="py-2 px-3 text-left">Series</th>
                                    <th class="py-2 px-3 text-right">Combustibles</th>
                                    <th class="py-2 px-3 text-right">Extras</th>
                                    <th class="py-2 px-3 text-right">Nota de Crédito</th>
                                    <th class="py-2 px-3 text-right">Diferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($seriesEdSuite)):
                                    $totalCombustible = 0;
                                    $totalExtras = 0;
                                    $totalNotas = 0;
                                    $totalDiferenciaEdSuite = 0;
                                    foreach ($seriesEdSuite as $row):
                                        $totalCombustible += $row['combustible'];
                                        $totalExtras += $row['extras'];
                                        $totalNotas += $row['nota_credito'];
                                        $totalDiferenciaEdSuite += $row['diferencia'];
                                        $diffClass = ($row['diferencia'] != 0) ? "bg-[#dc3545] text-white font-bold" : "";
                                ?>
                                        <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                            <td class="py-2 px-3 text-left"><?= htmlspecialchars($row['serie']) ?></td>
                                            <td class="py-2 px-3 text-right">S/. <?= number_format($row['combustible'], 2) ?></td>
                                            <td class="py-2 px-3 text-right">S/. <?= number_format($row['extras'], 2) ?></td>
                                            <td class="py-2 px-3 text-right">S/. <?= number_format($row['nota_credito'], 2) ?></td>
                                            <td class="py-2 px-3 text-right <?= $diffClass ?>">S/. <?= number_format($row['diferencia'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php
                                    $totalRowClass = ($totalDiferenciaEdSuite != 0) ? "bg-[#dc3545] text-white font-bold" : "bg-gray-100 font-bold";
                                    ?>
                                    <tr class="<?= $totalRowClass ?>">
                                        <td class="py-2 px-3">TOTAL</td>
                                        <td class="py-2 px-3 text-right">S/ <?= number_format($totalCombustible, 2) ?></td>
                                        <td class="py-2 px-3 text-right">S/. <?= number_format($totalExtras, 2) ?></td>
                                        <td class="py-2 px-3 text-right">S/. <?= number_format($totalNotas, 2) ?></td>
                                        <td class="py-2 px-3 text-right">S/. <?= number_format($totalDiferenciaEdSuite, 2) ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-gray-500">No hay ventas globales para este mes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- DIFERENCIAS Y SERIES AJENAS -->
                <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4">DIFERENCIAS Y SERIES AJENAS</h3>
                <div class="flex flex-col md:flex-row gap-8 mb-8 items-start justify-center">
                    <!-- Tabla DIFERENCIAS -->
                    <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white w-full md:w-[500px]">
                        <table class="w-full min-w-max text-sm">
                            <thead>
                                <tr class="bg-yellow-300 text-gray-900 font-bold">
                                    <th class="py-2 px-3 text-center text-sm" colspan="4">DIFERENCIAS</th>
                                </tr>
                                <tr class="bg-[#A9C3E8]">
                                    <th class="py-2 px-3 text-left">SERIE</th>
                                    <th class="py-2 px-3 text-right">TOTAL SIRE</th>
                                    <th class="py-2 px-3 text-right">TOTAL NUBOX</th>
                                    <th class="py-2 px-3 text-right">DIFERENCIA R.G</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalSire = 0;
                                $totalNubox = 0;
                                $totalDif = 0;
                                if (!empty($diferenciasNuboxSire)):
                                    foreach ($diferenciasNuboxSire as $row):
                                        $totalSire += $row['total_sire'];
                                        $totalNubox += $row['total_nubox'];
                                        $totalDif += $row['diferencia'];
                                        $rowClass = ($row['diferencia'] != 0) ? 'bg-[#dc3545] text-white font-bold' : '';
                                ?>
                                        <tr class="border-b border-[#2563EB] transition <?= $rowClass ?>">
                                            <td class="py-2 px-3 text-left font-semibold"><?= htmlspecialchars($row['serie']) ?></td>
                                            <td class="py-2 px-3 text-right"><?= number_format($row['total_sire'], 2) ?></td>
                                            <td class="py-2 px-3 text-right"><?= number_format($row['total_nubox'], 2) ?></td>
                                            <td class="py-2 px-3 text-right"><?= number_format($row['diferencia'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-gray-100 font-bold">
                                        <td class="py-2 px-3">TOTAL</td>
                                        <td class="py-2 px-3 text-right"><?= number_format($totalSire, 2) ?></td>
                                        <td class="py-2 px-3 text-right"><?= number_format($totalNubox, 2) ?></td>
                                        <td class="py-2 px-3 text-right"><?= number_format($totalDif, 2) ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-gray-500">No hay diferencias para este mes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabla SERIES AJENAS -->
                    <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white w-full md:w-[350px]">
                        <table class="w-full min-w-max text-sm">
                            <thead>
                                <tr class="bg-yellow-300 text-gray-900 font-bold">
                                    <th class="py-2 px-3 text-center text-sm" colspan="3">SERIES AJENAS</th>
                                </tr>
                                <tr class="bg-[#A9C3E8]">
                                    <th class="py-2 px-3 text-left">Serie</th>
                                    <th class="py-2 px-3 text-right">Conteo Total</th>
                                    <th class="py-2 px-3 text-right">Importe Total</th>
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
                                        <tr class="border-b border-[#2563EB] transition hover:bg-blue-50">
                                            <td class="py-2 px-3 text-left font-semibold"><?= htmlspecialchars($serie['serie']) ?></td>
                                            <td class="py-2 px-3 text-right"><?= number_format($serie['total_conteo'], 0) ?></td>
                                            <td class="py-2 px-3 text-right">S/ <?= number_format($serie['total_importe'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-gray-100 font-bold">
                                        <td class="py-2 px-3">TOTAL</td>
                                        <td class="py-2 px-3 text-right"><?= number_format($totalConteo, 0) ?></td>
                                        <td class="py-2 px-3 text-right">S/ <?= number_format($totalImporte, 2) ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-gray-500">No hay series ajenas para este mes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
        <?php endif;
        endif;
        ?>
    </div>
</div>
<script>
    window.MESES_HABILITADOS = <?= json_encode(array_column($mesesDisponibles, 'mes')) ?>;
    window.ESTABLECIMIENTOS_EXPORT = <?= json_encode(array_map(function ($e) use ($mesSeleccionado) {
                                            $tieneDatos = \Cuadre::datosEstablecimiento($e['id'], $mesSeleccionado);
                                            return [
                                                'id' => $e['id'],
                                                'etiqueta' => $e['etiqueta'],
                                                'tiene_datos' => $tieneDatos
                                            ];
                                        }, $establecimientos ?? [])) ?>;
</script>
<script src="../assets/js/reporte.js"></script>