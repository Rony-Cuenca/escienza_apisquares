<?php
require_once __DIR__ . '/../../helpers/permisos_helper.php';
?>
<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="w-full mx-auto bg-white rounded-lg shadow-2xl shadow-gray-300/40 p-8">
        <!-- Cabecera -->
        <div class="flex flex-col items-center w-full pb-6 border-b border-gray-200 mb-8">
            <h2 class="text-3xl font-bold text-gray-900 text-center uppercase">Reporte de Cuadres</h2>
        </div>

        <!-- Formulario de filtros -->
        <form method="GET" action="index.php" class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
            <input type="hidden" name="controller" value="reporte">
            <input type="hidden" name="action" value="index">
            <div class="flex flex-row gap-4 flex-1 items-end">
                <div>
                    <label class="block text-sm font-medium mb-1">Meses</label>
                    <input
                        type="text"
                        id="mesPicker"
                        name="mes"
                        class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 max-w-[180px] md:max-w-[160px]"
                        placeholder="Seleccione un mes"
                        value="<?= isset($_GET['mes']) ? htmlspecialchars($_GET['mes']) : '' ?>"
                        readonly
                        required
                        onchange="this.form.submit()"
                        autocomplete="off" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Establecimientos</label>
                    <select name="id_establecimiento" id="establecimientoPicker" class="border rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-400 truncate overflow-hidden whitespace-nowrap text-ellipsis" onchange="this.form.submit()">
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
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                        <img src="../src/images/ic_pdf.png" alt="PDF" class="w-5 h-5 bd-w">
                        Exportar PDF
                    </button>
                    <button
                        type="button"
                        onclick="exportarArchivo('Excel')"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                        <img src="../src/images/ic_excel.png" alt="EXCEL" class="w-5 h-5 bd-w">
                        Exportar Excel
                    </button>
                </div>
            <?php endif; ?>
        </form>

        <!-- Tabla SIRE -->
        <h3 class="text-xl font-bold text-gray-800 mt-8 mb-2">Resumen de Series - SIRE</h3>
        <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white mb-8">
            <table class="w-full min-w-max text-sm">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-3 text-left">Serie</th>
                        <th class="py-2 px-3 text-right">Cantidad</th>
                        <th class="py-2 px-3 text-right">Suma Gravada</th>
                        <th class="py-2 px-3 text-right">Suma Exonerada</th>
                        <th class="py-2 px-3 text-right">Suma Inafecta</th>
                        <th class="py-2 px-3 text-right">Suma IGV</th>
                        <th class="py-2 px-3 text-right">Suma Total</th>
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
                        ?>
                            <tr class="border-b border-[#2563EB] transition <?= $cuadre['tipo_comprobante'] == 3 ? 'bg-[#dc3545] text-white font-bold' : 'hover:bg-blue-50' ?>">
                                <td class="py-2 px-3 text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                <td class="py-2 px-3 text-right"><?= $cuadre['cantidad_compr'] ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['monto_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bg-gray-100 font-bold">
                            <td class="py-2 px-3">TOTAL</td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalCantidadSIRE, 0) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalGravadaSIRE, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalExoneradaSIRE, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalInafectaSIRE, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalIgvSIRE, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalMontoSIRE, 2) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="py-4 text-center text-gray-500">No hay cuadres SIRE para este mes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla NUBOX360 -->
        <h3 class="text-xl font-bold text-gray-800 mt-8 mb-2">Resumen de Series - NUBOX360</h3>
        <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white">
            <table class="w-full min-w-max text-sm">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-3 text-left">Serie</th>
                        <th class="py-2 px-3 text-left">Cantidad</th>
                        <th class="py-2 px-3 text-right">Suma Gravada</th>
                        <th class="py-2 px-3 text-right">Suma Exonerada</th>
                        <th class="py-2 px-3 text-right">Suma Inafecta</th>
                        <th class="py-2 px-3 text-right">Suma IGV</th>
                        <th class="py-2 px-3 text-right">Suma Total</th>
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
                        ?>
                            <tr class="border-b border-[#2563EB] transition <?= $cuadre['tipo_comprobante'] == 3 ? 'bg-[#dc3545] text-white font-bold' : 'hover:bg-blue-50' ?>">
                                <td class="py-2 px-3 text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                <td class="py-2 px-3 text-right"><?= $cuadre['cantidad_compr'] ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['monto_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bg-gray-100 font-bold">
                            <td class="py-2 px-3">TOTAL</td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalCantidadNUBOX, 0) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalGravadaNUBOX, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalExoneradaNUBOX, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalInafectaNUBOX, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalIgvNUBOX, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalMontoNUBOX, 2) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="py-4 text-center text-gray-500">No hay cuadres NUBOX360 para este mes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla EDSUITE -->
        <h3 class="text-xl font-bold text-gray-800 mt-8 mb-2">Resumen de Series - EDSUITE</h3>
        <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white mb-8">
            <table class="w-full min-w-max text-sm">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-3 text-left">Serie</th>
                        <th class="py-2 px-3 text-left">Cantidad</th>
                        <th class="py-2 px-3 text-right">Suma Gravada</th>
                        <th class="py-2 px-3 text-right">Suma Exonerada</th>
                        <th class="py-2 px-3 text-right">Suma Inafecta</th>
                        <th class="py-2 px-3 text-right">Suma IGV</th>
                        <th class="py-2 px-3 text-right">Suma Total</th>
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
                        ?>
                            <tr class="border-b border-[#2563EB] transition <?= $cuadre['tipo_comprobante'] == 3 ? 'bg-[#dc3545] text-white font-bold' : 'hover:bg-blue-50' ?>">
                                <td class="py-2 px-3 text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                <td class="py-2 px-3 text-right"><?= $cuadre['cantidad_compr'] ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['monto_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bg-gray-100 font-bold">
                            <td class="py-2 px-3">TOTAL</td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalCantidadEDSUITE, 0) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalGravadaEDSUITE, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalExoneradaEDSUITE, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalInafectaEDSUITE, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalIgvEDSUITE, 2) ?></td>
                            <td class="py-2 px-3 text-right"><?= number_format($totalMontoEDSUITE, 2) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="py-4 text-center text-gray-500">No hay cuadres EDSUITE para este mes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla SERIES AJENAS -->
        <h3 class="text-xl font-bold text-gray-800 mt-8 mb-2">Resumen de Series Ajenas</h3>
        <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white mb-8">
            <table class="w-full min-w-max text-sm">
                <thead>
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

        <!-- Tabla VENTAS GLOBALES -->
        <h3 class="text-xl font-bold text-gray-800 mt-8 mb-2">Resumen de Ventas Globales</h3>
        <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white mb-8">
            <table class="w-full min-w-max text-sm">
                <thead>
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

        <!-- RESUMEN FACTURAS, BOLETAS Y NOTAS DE CRÉDITO -->
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
                        <tr>
                            <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">SIRE</td>
                            <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc[2][2]) ? 'S/ ' . number_format($totalesTipoDoc[2][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr>
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
                        <tr>
                            <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">SIRE</td>
                            <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc[1][2]) ? 'S/ ' . number_format($totalesTipoDoc[1][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr>
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
                        <tr>
                            <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">SIRE</td>
                            <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc[3][2]) ? 'S/ ' . number_format($totalesTipoDoc[3][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr>
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

        <!-- RESUMEN DE SERIES Y DIFERENCIAS -->
        <h3 class="text-xl font-bold text-gray-800 mt-8 mb-2">Diferencias</h3>
        <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white mb-8">
            <table class="w-full min-w-max text-sm">
                <thead>
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
                    foreach ($diferenciasSeries as $row):
                        $totalSire += $row['total_sire'];
                        $totalNubox += $row['total_nubox'];
                        $totalDif += $row['diferencia'];
                        $rowClass = ($row['diferencia'] != 0) ? 'bg-[#dc3545] text-white font-bold' : '';
                    ?>
                        <tr class="border-b border-[#2563EB] hover:bg-blue-50 transition <?= $rowClass ?>">
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
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    window.MESES_HABILITADOS = <?= json_encode(array_column($mesesDisponibles, 'mes')) ?>;
</script>
<script src="../assets/js/reporte.js"></script>