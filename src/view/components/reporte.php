<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="flex flex-col items-center w-full">
        <div class="w-full max-w-5xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-8 uppercase">Reporte General</h2>
            <div class="flex flex-col md:flex-row md:justify-between gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Tipo de Reporte</label>
                    <select class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Selecciona un tipo</option>
                        <option value="ventas">Ventas</option>
                        <option value="usuarios">Usuarios</option>
                        <option value="establecimientos">Establecimientos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Fecha Desde</label>
                    <input type="date" class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Fecha Hasta</label>
                    <input type="date" class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div class="flex items-end">
                    <button class="bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold px-6 py-2 rounded-lg shadow transition-colors duration-200">
                        Buscar
                    </button>
                </div>
            </div>
            <div class="flex justify-end mb-4">
                <button class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Exportar a Excel
                </button>
            </div>
            <div class="overflow-x-auto rounded-xl border border-[#2563EB] bg-white">
                <table class="w-full min-w-max text-sm">
                    <thead>
                        <tr class="bg-[#A9C3E8]">
                            <th class="py-2 px-3 text-left font-semibold border-b border-[#2563EB]">#</th>
                            <th class="py-2 px-3 text-left font-semibold border-b border-[#2563EB]">Nombre</th>
                            <th class="py-2 px-3 text-left font-semibold border-b border-[#2563EB]">Tipo</th>
                            <th class="py-2 px-3 text-left font-semibold border-b border-[#2563EB]">Fecha</th>
                            <th class="py-2 px-3 text-left font-semibold border-b border-[#2563EB]">Estado</th>
                            <th class="py-2 px-3 text-left font-semibold border-b border-[#2563EB]">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <tr class="border-b border-[#2563EB] hover:bg-blue-50 transition">
                                <td class="py-2 px-3"><?= $i ?></td>
                                <td class="py-2 px-3">Ejemplo <?= $i ?></td>
                                <td class="py-2 px-3"><?= $i % 2 == 0 ? 'Ventas' : 'Usuarios' ?></td>
                                <td class="py-2 px-3"><?= date('Y-m-d', strtotime("-$i days")) ?></td>
                                <td class="py-2 px-3">
                                    <?php if ($i % 3 == 0): ?>
                                        <span class="inline-block px-2 py-1 rounded bg-green-200 text-green-800 text-xs font-medium">Completado</span>
                                    <?php else: ?>
                                        <span class="inline-block px-2 py-1 rounded bg-yellow-200 text-yellow-800 text-xs font-medium">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-3">
                                    <button class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs mr-2">Ver</button>
                                    <button class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">Eliminar</button>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-6">
                <span class="text-sm text-gray-700">Mostrando 1 a 8 de 8 registros</span>
                <div class="flex gap-2">
                    <button class="px-3 py-1 border rounded bg-gray-100 text-gray-700 hover:bg-gray-300">Anterior</button>
                    <button class="px-3 py-1 border rounded bg-[#2563EB] text-white hover:bg-blue-800">1</button>
                    <button class="px-3 py-1 border rounded bg-gray-100 text-gray-700 hover:bg-gray-300">Siguiente</button>
                </div>
            </div>
        </div>
    </div>
</div>