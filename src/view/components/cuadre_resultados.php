<?php if (!empty($ErrorSIRE) || !empty($ErrorNUBOX) || !empty($ErrorEDSUITE)) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
    <!-- Resultados de los cuadres -->
    <div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
        <div class="w-full bg-white rounded-lg shadow-2xl shadow-gray-300/40 p-2 md:p-8">
            <!-- Cabecera -->
            <div class="flex items-center justify-center w-full pb-6 px-6 border-b border-gray-200 mb-8">
                <h2 class="text-xl md:text-2xl font-semibold text-gray-800 text-center uppercase" style="font-family: 'Montserrat', sans-serif;">
                    Resultados de Cuadres
                </h2>
            </div>

            <!-- Contenido de resultados -->
            <div class="flex flex-col xl:flex-row gap-6 w-full mt-8">
                <?php if (!empty($ResultsSIRE)): ?>
                    <div class="w-full xl:w-1/2 min-w-0">
                        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 text-center">Resumen de Series - SIRE</h3>
                            </div>
                            <div class="p-4">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm border border-gray-300 rounded-lg overflow-hidden">
                                        <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                                            <tr>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Serie</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Conteo</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">BI Gravada</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Exonerado</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Inafecto</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">IGV</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ResultsSIRE as $resultado): ?>
                                                <tr class="hover:bg-gray-50 even:bg-gray-50">
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo $resultado['serie']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo $resultado['conteo']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo number_format($resultado['bi'], 2); ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo number_format($resultado['exonerado'], 2); ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo number_format($resultado['inafecto'], 2); ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo number_format($resultado['igv'], 2); ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 font-semibold whitespace-nowrap"><?php echo number_format($resultado['total'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="bg-gray-100">
                                            <tr>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Total:</td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo array_sum(array_column($ResultsSIRE, 'conteo')); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsSIRE, 'bi')), 2); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsSIRE, 'exonerado')), 2); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsSIRE, 'inafecto')), 2); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsSIRE, 'igv')), 2); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsSIRE, 'total')), 2); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($ResultsNUBOX)): ?>
                    <div class="w-full xl:w-1/2 min-w-0">
                        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 text-center">Resumen de Series - NUBOX360</h3>
                            </div>
                            <div class="p-4">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm border border-gray-300 rounded-lg overflow-hidden">
                                        <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                                            <tr>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Serie</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Conteo</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">BI Gravada</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Exonerado</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Inafecto</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">IGV</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ResultsNUBOX as $resultado): ?>
                                                <tr class="hover:bg-gray-50 even:bg-gray-50">
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo $resultado['serie']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo $resultado['conteo']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo number_format($resultado['bi'], 2); ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo number_format($resultado['exonerado'], 2); ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo number_format($resultado['inafecto'], 2); ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 whitespace-nowrap"><?php echo number_format($resultado['igv'], 2); ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 font-semibold whitespace-nowrap"><?php echo number_format($resultado['total'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="bg-gray-100">
                                            <tr>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Total:</td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo array_sum(array_column($ResultsNUBOX, 'conteo')); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsNUBOX, 'bi')), 2); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsNUBOX, 'exonerado')), 2); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsNUBOX, 'inafecto')), 2); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsNUBOX, 'igv')), 2); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsNUBOX, 'total')), 2); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($ResultsEDSUITE)): ?>
                <div class="w-full mt-8">
                    <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 text-center">Resumen de Series - EDSUITE</h3>
                        </div>
                        <div class="p-4">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm border border-gray-300 rounded-lg overflow-hidden">
                                    <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                                        <tr>
                                            <th class="border border-gray-300 px-3 py-2 text-center text-gray-900 font-bold whitespace-nowrap">Serie</th>
                                            <th class="border border-gray-300 px-3 py-2 text-center text-gray-900 font-bold whitespace-nowrap">Conteo</th>
                                            <th class="border border-gray-300 px-3 py-2 text-center text-gray-900 font-bold whitespace-nowrap">IGV</th>
                                            <th class="border border-gray-300 px-3 py-2 text-center text-gray-900 font-bold whitespace-nowrap">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ResultsEDSUITE as $resultado): ?>
                                            <tr class="hover:bg-gray-50 even:bg-gray-50">
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['serie']; ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['conteo']; ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo number_format($resultado['igv'], 2); ?></td>
                                                <td class="border border-gray-300 px-3 py-2 text-gray-900 font-semibold text-center whitespace-nowrap"><?php echo number_format($resultado['total'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-gray-100">
                                        <tr>
                                            <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center font-bold whitespace-nowrap">Total:</td>
                                            <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center font-bold whitespace-nowrap"><?php echo array_sum(array_column($ResultsEDSUITE, 'conteo')); ?></td>
                                            <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsEDSUITE, 'igv')), 2); ?></td>
                                            <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center font-bold whitespace-nowrap"><?php echo number_format(array_sum(array_column($ResultsEDSUITE, 'total')), 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="flex flex-col xl:flex-row gap-6 w-full mt-8">
                <?php if (!empty($ResultsValidarSeries)): ?>
                    <div class="w-full xl:w-1/2 min-w-0">
                        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 text-center">Validar Series - Series Ajenas</h3>
                            </div>
                            <div class="p-4">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm border border-gray-300 rounded-lg overflow-hidden">
                                        <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                                            <tr>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Serie</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Conteo</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Cuadres</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <?php foreach ($ResultsValidarSeries as $resultado): ?>
                                                <tr class="hover:bg-gray-50 even:bg-gray-50">
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['serie']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['conteo']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['cuadre']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center font-semibold whitespace-nowrap"><?php echo number_format($resultado['total'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($diferenciaGlobales)): ?>
                    <div class="w-full xl:w-1/2 min-w-0">
                        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 text-center">Diferencia Global</h3>
                            </div>
                            <div class="p-4">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm border border-gray-300 rounded-lg overflow-hidden">
                                        <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                                            <tr>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Serie</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Numero</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Total Sire</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Estado Sire</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Total Nubox</th>
                                                <th class="border border-gray-300 px-3 py-2 text-gray-900 font-bold whitespace-nowrap">Estado Nubox</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <?php foreach ($diferenciaGlobales as $resultado): ?>
                                                <tr class="hover:bg-gray-50 even:bg-gray-50">
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['sire']['serie']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['sire']['numero']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['sire']['total']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['sire']['estado']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['nubox']['total']; ?></td>
                                                    <td class="border border-gray-300 px-3 py-2 text-gray-900 text-center whitespace-nowrap"><?php echo $resultado['nubox']['estado']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            
            </div>
            
            <!-- Botón de acciones -->
            <div class="flex justify-center mt-8 pt-6 border-t border-gray-200">
                <button id="btnGuardarCuadres" 
                        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200"
                        <?php if (isset($_SESSION['resultsSerieArchivos']) && $_SESSION['resultsSerieArchivos'] != null): ?>
                            onclick="guardarCuadres()"
                        <?php else: ?>
                            onclick="window.location.href='index.php?controller=cuadres&action=cargarBD&user=<?php echo $id_usuario ?? '' ?>'"
                        <?php endif; ?>
                        >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Guardar Cuadres
                </button>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/../../model/Usuario.php';
require_once __DIR__ . '/../../model/Establecimiento.php';
require_once __DIR__ . '/../../helpers/sesion_helper.php';

// Usar el helper para obtener información del usuario de manera unificada
$id_usuario = SesionHelper::obtenerUsuarioActual();
$id_cliente = SesionHelper::obtenerClienteActual();

if ($id_usuario) {
    $user = Usuario::obtenerId($id_usuario);
    $establecimiento = Establecimiento::obtenerEstablecimientoPorCliente($id_cliente);
} else {
    $user = null;
    $establecimiento = null;
}
?>

    <script>
        function guardarCuadres() {
            const results = <?php echo json_encode(isset($_SESSION['resultsSerieArchivos']) ? $_SESSION['resultsSerieArchivos'] : []); ?>;
            const establecimientos = <?php echo json_encode($establecimiento); ?>;
            

            // Crear el HTML para mostrar los datos
            const htmlContent = `
                <div class="space-y-4">
                ${results.map((result, idx) => `
                    <div class="bg-white rounded-lg p-6 shadow border border-gray-200">
                    
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                        <h4 class="font-medium text-gray-800 text-lg md:text-xl">${result.series.join(', ')}</h4>
                    </div>
                    
                    <select class="establecimiento-select border border-gray-300 rounded-lg px-4 py-3 w-full text-base 
                            focus:outline-none focus:ring-2 focus:ring-blue-500 transition" data-index="${idx}">
                        <option value="">Seleccionar establecimiento</option>
                        ${establecimientos.map(est => `
                            <option value="${est.id}">${est.etiqueta}</option>
                        `).join('')}
                    </select>
                    
                    </div>
                `).join('')}
                </div>
            `;

            Swal.fire({
                title: 'Archivos de Series',
                html: htmlContent,
                showCancelButton: true,
                confirmButtonColor: '#4CAF50',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                    cancelButton: 'swal-cancel-button'
                },
                width: '800px'
            }).then((result) => {
                if (result.isConfirmed) {
                    const selects = document.querySelectorAll('.establecimiento-select');
                    selects.forEach(select => {
                        const index = parseInt(select.getAttribute('data-index'), 10);
                        const idEstablecimiento = select.value;
                        results[index].id_establecimiento = idEstablecimiento;
                    });
                    // Redirigir a cargarBD
                    postToCargarBD(results);
                }
            });
        }
        function postToCargarBD(results) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?controller=cuadres&action=cargarBD&user=<?php echo $id_usuario ?? '' ?>';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'resultsSerieArchivos';
            input.value = JSON.stringify(results);

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        // Script simplificado - el botón ahora está integrado en el HTML
        console.log('Resultados de cuadres cargados correctamente');
    </script>

<?php endif; ?>