<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="w-full mx-auto bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-8 uppercase">Reporte de Cuadres</h2>
        <form method="GET" action="index.php" class="flex flex-col md:flex-row md:justify-between gap-4 mb-6">
            <input type="hidden" name="controller" value="reporte">
            <input type="hidden" name="action" value="index">
            <div>
                <label class="block text-sm font-medium mb-1">Seleccionar Mes de Cuadre</label>
                <select name="mes" class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" onchange="this.form.submit()">
                    <option value="">Seleccione un mes</option>
                    <?php foreach ($mesesDisponibles as $mes): ?>
                        <option value="<?= $mes['mes'] ?>" <?= (isset($_GET['mes']) && $_GET['mes'] == $mes['mes']) ? 'selected' : '' ?>>
                            <?= $mes['mes_nombre'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <a href="index.php?controller=reporte&action=exportarPDF&mes=<?= urlencode($mesSeleccionado) ?>"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
                    Exportar PDF
                </a>
                <a href="index.php?controller=reporte&action=exportarExcel&mes=<?= urlencode($mesSeleccionado) ?>"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
                    Exportar Excel
                </a>
            </div>
        </form>

        <!-- Tabla SIRE -->
        <h3 class="text-xl font-bold text-gray-800 mt-8 mb-2">Resumen de Series - SIRE</h3>
        <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white mb-8">
            <table class="w-full min-w-max text-sm">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-3 text-left">#</th>
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
                        <?php foreach ($cuadresSIRE as $i => $cuadre): ?>
                            <tr class="border-b border-[#2563EB] transition <?= $cuadre['monto_total'] < 0 ? 'bg-[#dc3545] text-white' : 'hover:bg-blue-50' ?>">
                                <td class="py-2 px-3 text-left"><?= $i + 1 ?></td>
                                <td class="py-2 px-3 text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                <td class="py-2 px-3 text-right"><?= $cuadre['cantidad_compr'] ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                                <td class="py-2 px-3 text-right font-bold"><?= number_format($cuadre['monto_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="py-4 text-center text-gray-500">No hay cuadres NUBOX360 para este mes.</td>
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
                        <th class="py-2 px-3">#</th>
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
                        <?php foreach ($cuadresNUBOX as $i => $cuadre): ?>
                            <tr class="border-b border-[#2563EB] transition <?= $cuadre['monto_total'] < 0 ? 'bg-[#dc3545] text-white' : 'hover:bg-blue-50' ?>">
                                <td class="py-2 px-3 text-left"><?= $i + 1 ?></td>
                                <td class="py-2 px-3 text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                <td class="py-2 px-3 text-right"><?= $cuadre['cantidad_compr'] ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                                <td class="py-2 px-3 text-right font-bold"><?= number_format($cuadre['monto_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
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
                        <th class="py-2 px-3">#</th>
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
                        <?php foreach ($cuadresEDSUITE as $i => $cuadre): ?>
                            <tr class="border-b border-[#2563EB] transition <?= $cuadre['monto_total'] < 0 ? 'bg-[#dc3545] text-white' : 'hover:bg-blue-50' ?>">
                                <td class="py-2 px-3 text-left"><?= $i + 1 ?></td>
                                <td class="py-2 px-3 text-left"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                <td class="py-2 px-3 text-right"><?= $cuadre['cantidad_compr'] ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                                <td class="py-2 px-3 text-right"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                                <td class="py-2 px-3 text-right font-bold"><?= number_format($cuadre['monto_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="py-4 text-center text-gray-500">No hay cuadres EDSUITE para este mes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- RESUMEN FACTURAS Y BOLETAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- FACTURAS -->
            <div class="bg-white rounded-xl border border-[#2563EB] shadow overflow-hidden">
                <table class="w-full min-w-max text-sm">
                    <thead>
                        <tr class="bg-[#A9C3E8]">
                            <th colspan="2" class="py-3 px-4 text-left text-lg font-bold border-b border-[#2563EB]">FACTURAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover:bg-blue-50">
                            <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">SIRE</td>
                            <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc['FACTURA'][2]) ? 'S/ ' . number_format($totalesTipoDoc['FACTURA'][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr class="hover:bg-blue-50">
                            <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">NUBOX</td>
                            <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc['FACTURA'][1]) ? 'S/ ' . number_format($totalesTipoDoc['FACTURA'][1], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <?php
                        $faltanteFact = (isset($totalesTipoDoc['FACTURA'][2]) ? $totalesTipoDoc['FACTURA'][2] : 0) - (isset($totalesTipoDoc['FACTURA'][1]) ? $totalesTipoDoc['FACTURA'][1] : 0);
                        if ($faltanteFact == 0) {
                            $faltanteClass = 'bg-[#bbf7d0] text-[#166534]';
                        } elseif ($faltanteFact != 0) {
                            $faltanteClass = 'bg-[#dc3545] text-white';
                        } else {
                            $faltanteClass = 'bg-white';
                        }
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
                        <tr class="hover:bg-blue-50">
                            <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">SIRE</td>
                            <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc['BOLETA'][2]) ? 'S/ ' . number_format($totalesTipoDoc['BOLETA'][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr class="hover:bg-blue-50">
                            <td class="py-2 px-4 font-semibold border-b border-[#2563EB]">NUBOX</td>
                            <td class="py-2 px-4 text-right border-b border-[#2563EB]"><?= isset($totalesTipoDoc['BOLETA'][1]) ? 'S/ ' . number_format($totalesTipoDoc['BOLETA'][1], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <?php
                        $faltanteBoleta = (isset($totalesTipoDoc['BOLETA'][2]) ? $totalesTipoDoc['BOLETA'][2] : 0) - (isset($totalesTipoDoc['BOLETA'][1]) ? $totalesTipoDoc['BOLETA'][1] : 0);
                        if ($faltanteBoleta == 0) {
                            $faltanteClassB = 'bg-[#bbf7d0] text-[#166534]';
                        } elseif ($faltanteBoleta != 0) {
                            $faltanteClassB = 'bg-[#dc3545] text-white';
                        } else {
                            $faltanteClassB = 'bg-white';
                        }
                        ?>
                        <tr class="font-bold <?= $faltanteClassB ?>">
                            <td class="py-2 px-4 border-t border-[#2563EB]">FALTANTE</td>
                            <td class="py-2 px-4 text-right border-t border-[#2563EB]">S/ <?= number_format($faltanteBoleta, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RESUMEN DE SERIES Y DIFERENCIAS -->
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
                    ?>
                        <tr class="border-b border-[#2563EB] hover:bg-blue-50 transition">
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