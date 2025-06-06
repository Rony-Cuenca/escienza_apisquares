
<div class="flex flex-col items-center w-full px-2 pt-6 pb-2">
    <div class="w-full">
        <div class="mx-auto bg-white rounded-lg shadow-lg overflow-hidden max-w-7xlº    ">
            <div class="p-8">
                <h2 class="text-4xl font-semibold text-gray-900 text-center mb-8 uppercase">Comparación de Reportes</h2>
                <form action="index.php?controller=cuadres&action=cuadre&user=<?php echo $_SESSION['id_usuario'] ?>" method="post" enctype="multipart/form-data">
                    <div class="flex gap-4 mb-6">
                        <div class="flex-1">
                            <label class="block font-medium mb-1">Archivo SIRE</label>
                            <div class="flex">
                                <input type="text" id="file-sire" class="flex-grow px-2 py-2 border rounded-l-lg bg-gray-50" readonly placeholder="Ningún archivo seleccionado">
                                <label for="sire" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-r-lg border border-l-0 hover:bg-gray-300">
                                    Seleccionar archivo
                                </label>
                                <input type="file" name="exe_sire" id="sire" accept=".csv" class="hidden" onchange="update(this, 'file-sire')" required>
                            </div>
                        </div>
                        <div class="flex-1">
                            <label class="block font-medium mb-1">Archivo Nubox360</label>
                            <div class="flex">
                                <input type="text" id="file-nubox" class="flex-grow px-2 py-2 border rounded-l-lg bg-gray-50" readonly placeholder="Ningún archivo seleccionado">
                                <label for="nubox" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-r-lg border border-l-0 hover:bg-gray-300">
                                    Seleccionar archivo
                                </label>
                                <input type="file" name="exe_nubox" id="nubox" accept=".xlsx" class="hidden" onchange="update(this, 'file-nubox')" required>
                            </div>
                        </div>
                        <div class="flex-1">
                            <label class="block font-medium mb-1">Archivo EDSuite</label>
                            <div class="flex">
                                <input type="text" id="" class="flex-grow px-2 py-2 border rounded-l-lg bg-gray-50" readonly placeholder="Ningún archivo seleccionado">
                                <label for="" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-r-lg border border-l-0 hover:bg-gray-300">
                                    Seleccionar archivo
                                </label>
                                <input type="file" name="" id="" accept="" class="hidden" onchange="">
                            </div>
                        </div>
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
        <?php include __DIR__ . '/cuadre_resultados.php'; ?>
    </div>
</div>

<script>
function update(input, displayId) {
    const displayElement = document.getElementById(displayId);
    displayElement.value = input.files[0] ? input.files[0].name : 'Ningún archivo seleccionado';
}
</script>