    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-8">
                <h2 class="text-4xl font-semibold text-gray-900 text-center mb-8">Comparación de Reportes</h2>
                <form action="index.php?controller=cuadres&action=cuadre" method="post" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Archivo SIRE</label>
                        <input type="file" name="exe_sire" id="sire" accept=".csv">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Archivo Nubox360</label>
                        <input type="file" name="exe_nubox" id="nubox" accept=".xlsx" >
                    </div>

                    <div class="text-center">
                        <button type="submit" 
                                class="py-2 px-6 border text-white rounded-lg bg-blue-600 hover:bg-blue-700 
                                focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Subir Archivos
                        </button>
                    </div>
                </form>
            </div>
        </div>
            <!-- Mostrar resultados -->
            <?php include __DIR__ . '/cuadre_resultados.php'; ?>
    </div>