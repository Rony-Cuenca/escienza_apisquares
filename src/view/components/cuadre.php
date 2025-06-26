<div class="w-full items-center px-2 md:px-5 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="flex flex-col items-center w-full">
        <div class="w-full">
            <div class="mx-auto bg-white rounded-lg shadow-lg overflow-hidden max-w-7xl">
                <div class="p-8">
                    <div class="w-full flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
                        <h2 class="text-2xl md:text-4xl font-bold text-gray-800 text-center w-full md:w-auto uppercase">
                            Comparación de Reportes
                        </h2>
                        <button onclick="abrirModal()" id="btnNuevoEstablecimiento" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold 
                                py-2.5 px-5 rounded-xl shadow-md transition duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586A4 4 0 0015.172 7z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 3h5v5" />
                            </svg>
                            Unir Excel
                        </button>
                    </div>
                    <form action="index.php?controller=cuadres&action=cuadre&user=<?php echo $_SESSION['id_usuario'] ?>" method="post" enctype="multipart/form-data">
                        <div class="flex gap-4 mb-6 mt-12">
                            <div class="flex-1">
                                <label class="block font-medium mb-1">Archivo SIRE</label>
                                <div class="flex">
                                    <input type="text" id="file-sire" class="flex-grow px-2 py-2 border rounded-l-lg bg-gray-50" readonly placeholder="Ningún archivo seleccionado">
                                    <label for="sire" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-r-lg border border-l-0 hover:bg-gray-300">
                                        Seleccionar archivo
                                    </label>
                                    <input type="file" name="exe_sire" id="sire" accept=".csv" class="hidden" onchange="update(this, 'file-sire')">
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="block font-medium mb-1">Archivo Nubox360</label>
                                <div class="flex">
                                    <input type="text" id="file-nubox" class="flex-grow px-2 py-2 border rounded-l-lg bg-gray-50" readonly placeholder="Ningún archivo seleccionado">
                                    <label for="nubox" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-r-lg border border-l-0 hover:bg-gray-300">
                                        Seleccionar archivo
                                    </label>
                                    <input type="file" name="exe_nubox" id="nubox" accept=".xlsx" class="hidden" onchange="update(this, 'file-nubox')">
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="block font-medium mb-1">Archivo EDSuite</label>
                                <div class="flex">
                                    <input type="text" id="file-edsuite" class="flex-grow px-2 py-2 border rounded-l-lg bg-gray-50" readonly placeholder="Ningún archivo seleccionado">
                                    <label for="edsuite" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-r-lg border border-l-0 hover:bg-gray-300">
                                        Seleccionar archivo
                                    </label>
                                    <input type="file" name="exe_edsuite" id="edsuite" accept=".xlsx" class="hidden" onchange="update(this, 'file-edsuite')">
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
</div>

<script>
    function update(input, displayId) {
        const displayElement = document.getElementById(displayId);
        displayElement.value = input.files[0] ? input.files[0].name : 'Ningún archivo seleccionado';
    }
    function abrirModal() {
        document.getElementById('modalUnirExcel').classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modalUnirExcel').classList.add('hidden');
    }
</script>


<!-- Modal -->
<div id="modalUnirExcel" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-xl w-auto max-w-4xl p-6 relative">
        <!-- Botón Cerrar -->
        <button onclick="cerrarModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <h3 class="text-xl font-semibold mb-4 text-gray-800 text-center uppercase">Unir Archivos</h3>

        <form action="index.php?controller=cuadres&action=unirExcel" method="post" enctype="multipart/form-data">
            <div class="mb-6">
                <div class="flex justify-center">
                    <label class="flex items-center gap-4 px-4 py-2 border border-gray-300 rounded-lg bg-white shadow-sm cursor-pointer hover:bg-gray-50 transition">
                        <input type="file" name="archivos_excel[]" id="files_unir" accept=".xlsx" multiple class="hidden">
                        <span class="bg-green-100 text-green-700 font-semibold text-sm px-3 py-1 rounded">Elegir archivos</span>
                        <span id="cantidadArchivos" class="text-sm text-gray-700">Ningún archivo seleccionado</span>
                    </label>
                </div>
            </div>

            <!-- Previsualización de archivos (inicia oculto) -->
            <div id="previewFiles" class="hidden bg-blue-50 p-4 border border-dashed border-blue-400 rounded-lg text-center transition-all duration-300 ease-in-out">
                <div class="flex flex-wrap justify-center gap-4" id="fileCardsContainer">
                    <!-- Tarjetas generadas por JS -->
                </div>
            </div>

            <!-- Botón para enviar (inicia oculto) -->
            <div id="submitBtnContainer" class="text-center mt-6 hidden">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg shadow">
                    Unificar
                </button>
            </div>
            <?php if (isset($_GET['modal']) && $_GET['modal'] === 'unificacionExitosa' && isset($_GET['archivo'])): ?>
                <script>
                    window.addEventListener('DOMContentLoaded', () => {
                        const modal = document.getElementById('modalUnirExcel');
                        modal.classList.remove('hidden');

                        const submitBtnContainer = document.getElementById('submitBtnContainer');
                        if (submitBtnContainer) {
                            submitBtnContainer.innerHTML = `
                                <a href="http://localhost:5000/descargas/<?php echo urlencode($_GET['archivo']); ?>" 
                                target="_blank"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow mr-3 inline-block">
                                    Descargar Excel
                                </a>
                                <button onclick="cerrarModal()" 
                                        class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-2 rounded-lg shadow">
                                    Cargar Excel
                                </button>
                            `;
                            submitBtnContainer.classList.remove('hidden');
                        }
                    });
                </script>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Icono Excel de fondo con JS -->
<script>
document.getElementById('files_unir').addEventListener('change', function (e) {
    const count = this.files.length;
    const label = document.getElementById('cantidadArchivos');
    if (count > 0) {
        label.textContent = `${count} archivo${count > 1 ? 's' : ''}`;
    } else {
        label.textContent = 'Ningún archivo seleccionado';
    }
    const files = Array.from(e.target.files);
    const container = document.getElementById('fileCardsContainer');
    const preview = document.getElementById('previewFiles');
    const btn = document.getElementById('submitBtnContainer');

    container.innerHTML = ''; // Limpiar anteriores

    if (files.length > 0) {
        preview.classList.remove('hidden');
        btn.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
        btn.classList.add('hidden');
    }

    files.forEach(file => {
        const card = document.createElement('div');
        card.className = "w-32 h-40 bg-center bg-no-repeat bg-contain bg-[url('https://cdn-icons-png.flaticon.com/512/732/732220.png')] flex flex-col justify-end items-center text-sm bg-gray-100 p-2 rounded-md shadow relative";

        const name = document.createElement('p');
        name.className = "text-xs mt-2 truncate w-full text-center";
        name.textContent = file.name;

        const size = document.createElement('span');
        size.className = "text-xs font-semibold bg-white/80 rounded px-2 py-0.5 mt-1";
        size.textContent = (file.size / (1024 * 1024)).toFixed(1) + ' MB';

        card.appendChild(size);
        card.appendChild(name);
        container.appendChild(card);
    });
});
</script>


