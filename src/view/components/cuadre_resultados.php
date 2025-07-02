<?php if (!empty($ErrorSIRE) || !empty($ErrorNUBOX) || !empty($ErrorEDSUITE)) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                html: `
                    <?php 
                        if (!empty($ErrorSIRE)) {
                            echo addslashes($ErrorSIRE);
                        } elseif (!empty($ErrorNUBOX)) {
                            echo addslashes($ErrorNUBOX);
                        } elseif (!empty($ErrorEDSUITE)) {
                            echo addslashes($ErrorEDSUITE);
                        }
                    ?>
                `,
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#e3342f'
            });
        });
    </script>
<?php endif; ?>

<?php if (isset($ResultsSIRE) || isset($ResultsNUBOX) || isset($ResultsEDSUITE)) : ?>
    <div class="flex flex-col xl:flex-row gap-8 mx-auto w-full mt-8">
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
    <?php if (!empty($ResultsEDSUITE)): ?>
        <div class="p-8">
            <div class="mx-auto bg-white rounded-lg shadow-lg overflow-hidden max-w-7xl">
                <div class="p-4">
                    <h3 class="text-2xl text-center font-semibold text-gray-900 mb-4">Resumen de Series - EDSUITE</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full whitespace-nowrap text-center align-middle">
                            <thead class="bg-gray-50 divide-y divide-gray-200 uppercase">
                                <tr>
                                    <th class="px-4 py-2 font-medium">Serie</th>
                                    <th class="px-4 py-2 font-medium">Conteo</th>
                                    <th class="px-4 py-2 font-medium">IGV</th>
                                    <th class="px-4 py-2 font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($ResultsEDSUITE as $resultado): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['serie']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['conteo']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo number_format($resultado['igv'], 2); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900 font-semibold"><?php echo number_format($resultado['total'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($ResultsValidarSeries)): ?>
        <div class="p-8">
            <div class="mx-auto bg-white rounded-lg shadow-lg overflow-hidden max-w-7xl">
                <div class="p-4">
                    <h3 class="text-2xl text-center font-semibold text-gray-900 mb-4">Validar Series - Series Ajenas</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full whitespace-nowrap text-center align-middle">
                            <thead class="bg-gray-50 divide-y divide-gray-200 uppercase">
                                <tr>
                                    <th class="px-4 py-2 font-medium">Serie</th>
                                    <th class="px-4 py-2 font-medium">Conteo</th>
                                    <th class="px-4 py-2 font-medium">Cuadres</th>
                                    <th class="px-4 py-2 font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($ResultsValidarSeries as $resultado): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['serie']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['conteo']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['cuadre']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $resultado['total']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('botonesContainer');

            // Verificar si ya existe el nuevo botón para no duplicarlo
            if (!document.getElementById('btnNuevo')) {
                const nuevoBtn = document.createElement('button');
                nuevoBtn.id = 'btnNuevo';
                nuevoBtn.type = 'button';
                nuevoBtn.className = 'ml-4 py-2 px-6 border text-white rounded-lg bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500';
                nuevoBtn.textContent = 'Guardar Cuadres';

                // Acción personalizada
                nuevoBtn.addEventListener('click', function () {
                    window.location.href = 'index.php?controller=cuadres&action=cargarBD&user=<?php echo $_SESSION['id_usuario'] ?>';
                });

                container.appendChild(nuevoBtn);
            }
        });
    </script>

<?php endif; ?>

