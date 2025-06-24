<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="flex flex-col items-center w-full">
        <div class="w-full max-w-5xl mx-auto bg-white rounded-lg shadow-lg p-8">
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
                </div>
            </form>

            <!-- Tabla SIRE -->
            <h3 class="text-xl font-bold text-gray-800 mt-8 mb-2">Resumen de Series - SIRE</h3>
            <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white mb-8">
                <table class="w-full min-w-max text-sm">
                    <thead>
                        <tr class="bg-[#A9C3E8]">
                            <th class="py-2 px-3">#</th>
                            <th class="py-2 px-3">Serie</th>
                            <th class="py-2 px-3">Cantidad</th>
                            <th class="py-2 px-3">Suma Gravada</th>
                            <th class="py-2 px-3">Suma Exonerada</th>
                            <th class="py-2 px-3">Suma Inafecta</th>
                            <th class="py-2 px-3">Suma IGV</th>
                            <th class="py-2 px-3">Suma Total</th>
                            <th class="py-2 px-3">Fecha de Cuadre</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cuadresSIRE)): ?>
                            <?php foreach ($cuadresSIRE as $i => $cuadre): ?>
                                <tr class="border-b border-[#2563EB] hover:bg-blue-50 transition">
                                    <td class="py-2 px-3"><?= $i + 1 ?></td>
                                    <td class="py-2 px-3"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                    <td class="py-2 px-3"><?= $cuadre['cantidad_compr'] ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                                    <td class="py-2 px-3 font-bold"><?= number_format($cuadre['monto_total'], 2) ?></td>
                                    <td class="py-2 px-3"><?= date('Y-m-d', strtotime($cuadre['fecha_registro'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
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
                            <th class="py-2 px-3">#</th>
                            <th class="py-2 px-3">Serie</th>
                            <th class="py-2 px-3">Cantidad</th>
                            <th class="py-2 px-3">Suma Gravada</th>
                            <th class="py-2 px-3">Suma Exonerada</th>
                            <th class="py-2 px-3">Suma Inafecta</th>
                            <th class="py-2 px-3">Suma IGV</th>
                            <th class="py-2 px-3">Suma Total</th>
                            <th class="py-2 px-3">Fecha de Cuadre</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cuadresNUBOX)): ?>
                            <?php foreach ($cuadresNUBOX as $i => $cuadre): ?>
                                <tr class="border-b border-[#2563EB] hover:bg-blue-50 transition">
                                    <td class="py-2 px-3"><?= $i + 1 ?></td>
                                    <td class="py-2 px-3"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                    <td class="py-2 px-3"><?= $cuadre['cantidad_compr'] ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                                    <td class="py-2 px-3 font-bold"><?= number_format($cuadre['monto_total'], 2) ?></td>
                                    <td class="py-2 px-3"><?= date('Y-m-d', strtotime($cuadre['fecha_registro'])) ?></td>
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
                            <th class="py-2 px-3">Serie</th>
                            <th class="py-2 px-3">Cantidad</th>
                            <th class="py-2 px-3">Suma Gravada</th>
                            <th class="py-2 px-3">Suma Exonerada</th>
                            <th class="py-2 px-3">Suma Inafecta</th>
                            <th class="py-2 px-3">Suma IGV</th>
                            <th class="py-2 px-3">Suma Total</th>
                            <th class="py-2 px-3">Fecha de Cuadre</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cuadresEDSUITE)): ?>
                            <?php foreach ($cuadresEDSUITE as $i => $cuadre): ?>
                                <tr class="border-b border-[#2563EB] hover:bg-blue-50 transition">
                                    <td class="py-2 px-3"><?= $i + 1 ?></td>
                                    <td class="py-2 px-3"><?= htmlspecialchars($cuadre['serie']) ?></td>
                                    <td class="py-2 px-3"><?= $cuadre['cantidad_compr'] ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_gravada'], 2) ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_exonerada'], 2) ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_inafecto'], 2) ?></td>
                                    <td class="py-2 px-3"><?= number_format($cuadre['suma_igv'], 2) ?></td>
                                    <td class="py-2 px-3 font-bold"><?= number_format($cuadre['monto_total'], 2) ?></td>
                                    <td class="py-2 px-3"><?= date('Y-m-d', strtotime($cuadre['fecha_registro'])) ?></td>
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
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-lg font-bold mb-2">FACTURAS</h3>
                    <table class="w-full text-sm mb-2">
                        <tr>
                            <td class="font-semibold">SIRE</td>
                            <td class="text-right"><?= isset($totalesTipoDoc['FACTURA'][2]) ? 'S/ ' . number_format($totalesTipoDoc['FACTURA'][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold">NUBOX</td>
                            <td class="text-right"><?= isset($totalesTipoDoc['FACTURA'][1]) ? 'S/ ' . number_format($totalesTipoDoc['FACTURA'][1], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr class="bg-yellow-100 font-bold">
                            <td>FALTANTE</td>
                            <td class="text-right">
                                S/ <?= number_format(
                                        (isset($totalesTipoDoc['FACTURA'][2]) ? $totalesTipoDoc['FACTURA'][2] : 0) -
                                            (isset($totalesTipoDoc['FACTURA'][1]) ? $totalesTipoDoc['FACTURA'][1] : 0),
                                        2
                                    ) ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- BOLETAS -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-lg font-bold mb-2">BOLETAS</h3>
                    <table class="w-full text-sm mb-2">
                        <tr>
                            <td class="font-semibold">SIRE</td>
                            <td class="text-right"><?= isset($totalesTipoDoc['BOLETA'][2]) ? 'S/ ' . number_format($totalesTipoDoc['BOLETA'][2], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold">NUBOX</td>
                            <td class="text-right"><?= isset($totalesTipoDoc['BOLETA'][1]) ? 'S/ ' . number_format($totalesTipoDoc['BOLETA'][1], 2) : 'S/ 0.00' ?></td>
                        </tr>
                        <tr class="bg-yellow-100 font-bold">
                            <td>FALTANTE</td>
                            <td class="text-right">
                                S/ <?= number_format(
                                        (isset($totalesTipoDoc['BOLETA'][2]) ? $totalesTipoDoc['BOLETA'][2] : 0) -
                                            (isset($totalesTipoDoc['BOLETA'][1]) ? $totalesTipoDoc['BOLETA'][1] : 0),
                                        2
                                    ) ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- RESUMEN DE SERIES Y DIFERENCIAS -->
            <div class="bg-white rounded-lg shadow p-4 mb-8">
                <h3 class="text-lg font-bold mb-2">RESUMEN DE SERIES Y DIFERENCIAS (SIRE vs NUBOX)</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-3">SERIE</th>
                            <th class="py-2 px-3">TOTAL SIRE</th>
                            <th class="py-2 px-3">TOTAL NUBOX</th>
                            <th class="py-2 px-3">DIFERENCIA R.G</th>
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
                                <td class="py-2 px-3 font-semibold"><?= htmlspecialchars($row['serie']) ?></td>
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
</div>