<?php if (!empty($ErrorSIRE)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 mt-10 py-3 rounded relative mb-6" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($ErrorSIRE); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($ErrorNUBOX)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 mt-10 py-3 rounded relative mb-6" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($ErrorNUBOX); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($ResultsSIRE) || !empty($ResultsNUBOX)): ?>
    <div class="flex flex-col md:flex-row justify-center gap-8 mx-auto max-w-7xl px-4">

        <?php if (!empty($ResultsSIRE)): ?>
            <div class="flex-1 bg-white rounded-lg shadow-md overflow-hidden mt-8">
                <div class="p-4">
                    <h3 class="text-2xl text-center font-semibold text-gray-900 mb-4">Resumen de Series - SIRE</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed text-sm text-gray-900">
                            <thead class="bg-gray-50 text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium">Serie</th>
                                    <th class="px-4 py-2 text-left font-medium">Conteo</th>
                                    <th class="px-4 py-2 text-left font-medium">BI Gravada</th>
                                    <th class="px-4 py-2 text-left font-medium">Exonerado</th>
                                    <th class="px-4 py-2 text-left font-medium">Inafecto</th>
                                    <th class="px-4 py-2 text-left font-medium">IGV</th>
                                    <th class="px-4 py-2 text-left font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($ResultsSIRE as $resultado): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['serie']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['conteo']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo number_format($resultado['bi'], 2); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo number_format($resultado['exonerado'], 2); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo number_format($resultado['inafecto'], 2); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo number_format($resultado['igv'], 2); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900 font-semibold"><?php echo number_format($resultado['total'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($ResultsNUBOX)): ?>
            <div class="flex-1 bg-white rounded-lg shadow-md overflow-hidden mt-8">
                <div class="p-4">
                    <h3 class="text-2xl text-center font-semibold text-gray-900 mb-4">Resumen de Series - NUBOX360</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed text-sm text-gray-900">
                            <thead class="bg-gray-50 text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium">Serie</th>
                                    <th class="px-4 py-2 text-left font-medium">Conteo</th>
                                    <th class="px-4 py-2 text-left font-medium">BI Gravada</th>
                                    <th class="px-4 py-2 text-left font-medium">Exonerado</th>
                                    <th class="px-4 py-2 text-left font-medium">Inafecto</th>
                                    <th class="px-4 py-2 text-left font-medium">IGV</th>
                                    <th class="px-4 py-2 text-left font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($ResultsNUBOX as $resultado): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['serie']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['conteo']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo number_format($resultado['bi'], 2); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo number_format($resultado['exonerado'], 2); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo number_format($resultado['inafecto'], 2); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo number_format($resultado['igv'], 2); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900 font-semibold"><?php echo number_format($resultado['total'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
<?php endif; ?>
