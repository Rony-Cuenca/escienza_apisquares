<?php if (!empty($ErrorSIRE) || !empty($ErrorNUBOX)): ?>
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-sm w-full mx-4 text-center shadow-lg">
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">
                        <?php 
                        if (!empty($ErrorSIRE)) {
                            echo htmlspecialchars($ErrorSIRE);
                        } else {
                            echo htmlspecialchars($ErrorNUBOX);
                        }
                        ?>
                    </h3>
                </div>
            </div>
            <div class="mt-4">
                <button onclick="closeModal()" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    <style>
        #errorModal {
            display: none;
        }
        #errorModal.show {
            display: flex;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('errorModal');
            modal.classList.add('show');
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        });

        function closeModal() {
            const modal = document.getElementById('errorModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    </script>
<?php endif; ?>

<?php if (!empty($ResultsSIRE) || !empty($ResultsNUBOX)): ?>
    <div class="flex flex-col xl:flex-row gap-8 mx-auto w-full px-4 mt-8">
        <?php if (!empty($ResultsSIRE)): ?>
            <div class="w-full xl:w-1/2 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4">
                    <h3 class="text-2xl text-center font-semibold text-gray-900 mb-4">Resumen de Series - SIRE</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full whitespace-nowrap">
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
            <div class="w-full xl:w-1/2 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4">
                    <h3 class="text-2xl text-center font-semibold text-gray-900 mb-4">Resumen de Series - NUBOX360</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full whitespace-nowrap">
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
