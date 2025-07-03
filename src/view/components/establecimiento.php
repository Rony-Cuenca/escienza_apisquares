<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="flex items-center justify-between w-full pt-6 pb-10 px-8 rounded-t-lg"
        style="background: linear-gradient(to bottom, #60A5FA 80%, #fff 100%);">
        <span class="text-xl text-white" style="font-family: 'Montserrat', sans-serif;">LISTA DE ESTABLECIMIENTOS</span>
        <div class="flex flex-col items-end gap-2">
            <button id="btnSincronizar"
                class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-normal py-2 px-4 rounded-lg shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sincronizar con SUNAT
            </button>
        </div>
    </div>
    <div class="w-full bg-white rounded-b-lg shadow-2xl shadow-blue-300 p-2 md:p-8">
        <!-- Tabla de Establecimientos -->
        <div class="mb-8 overflow-x-auto rounded-xl border border-[#2563EB]" style="font-family: 'Poppins', sans-serif; font-weight: 300;">
            <table class="w-full min-w-max bg-white text-sm">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-1 text-center font-semibold border-b border-[#2563EB] w-[40px]"><strong>#</strong></th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[80px]">Código</th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[100px]">Tipo</th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[200px]">Razón Social</th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[220px]">Dirección</th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[50px]">Estado</th>
                        <th class="py-2 px-1 border-b border-[#2563EB] w-[10px]"><strong></strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($establecimientos)): ?>
                        <?php $i = 1;
                        foreach ($establecimientos as $row): ?>
                            <tr class="border-b border-[#2563EB] hover:bg-blue-50 transition" style="font-size: small;">
                                <td class="py-2 px-1 text-center"><?= $i++ ?></td>
                                <td class="py-2 px-1">
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                        <?= htmlspecialchars($row['codigo_establecimiento'] ?? '0000') ?>
                                    </span>
                                </td>
                                <td class="py-2 px-1">
                                    <span class="text-xs <?= $row['tipo_establecimiento'] === 'MATRIZ' ? 'text-blue-600 font-semibold' : 'text-gray-600' ?>">
                                        <?= htmlspecialchars($row['tipo_establecimiento'] ?? 'MATRIZ') ?>
                                    </span>
                                </td>
                                <td class="py-2 px-1"><?= htmlspecialchars($row['razon_social']) ?></td>
                                <td class="py-2 px-1"><?= !empty($row['direccion']) ? htmlspecialchars($row['direccion']) : 'Sin establecer' ?></td>
                                <td class="py-2 px-1">
                                    <?php if ($row['estado'] == 1): ?>
                                        <span
                                            class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-green-200 text-green-800 text-sm font-medium cursor-pointer transition hover:bg-green-300"
                                            data-action="cambiarEstado"
                                            data-id="<?= $row['id'] ?>"
                                            data-estado="2"
                                            title="Cambiar a inactivo"
                                            tabindex="0"
                                            role="button"
                                            aria-pressed="true">
                                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Activo
                                        </span>
                                    <?php elseif ($row['estado'] == 2): ?>
                                        <span
                                            class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-red-200 text-yellow-800 text-sm font-medium cursor-pointer transition hover:bg-red-300"
                                            data-action="cambiarEstado"
                                            data-id="<?= $row['id'] ?>"
                                            data-estado="1"
                                            title="Cambiar a activo"
                                            tabindex="0"
                                            role="button"
                                            aria-pressed="true">
                                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>Inactivo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm font-medium cursor-not-allowed opacity-90">
                                            Deshabilitado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-1 text-center relative">
                                    <button data-action="toggleMenu" type="button" class="focus:outline-none" aria-label="Abrir menú de usuario">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24">
                                            <circle cx="12" cy="5" r="2" fill="#000" />
                                            <circle cx="12" cy="12" r="2" fill="#000" />
                                            <circle cx="12" cy="19" r="2" fill="#000" />
                                        </svg>
                                    </button>
                                    <div id="menuEstablecimiento" class="hidden fixed z-50 w-32 bg-white rounded-lg shadow-lg border min-w-[120px]">
                                        <?php if ($row['estado'] != 3): ?>
                                            <a href="javascript:void(0);" data-action="cambiarEstado" data-id="<?= $row['id'] ?>" data-estado="3"
                                                class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">Deshabilitar</a>
                                        <?php else: ?>
                                            <a href="javascript:void(0);" data-action="cambiarEstado" data-id="<?= $row['id'] ?>" data-estado="1"
                                                class="block w-full text-left px-4 py-2 text-green-600 hover:bg-gray-100">Habilitar</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-gray-500">No hay establecimientos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Paginación -->
        <div class="flex items-center justify-between bg-white" style="font-family: 'Montserrat', sans-serif; font-weight: lighter;">
            <div class="text-sm text-black">
                Mostrando <?= $offset + 1 ?> a <?= min($offset + $limit, $total) ?> de <?= $total ?> establecimientos
            </div>
            <div class="flex gap-2">
                <?php for ($i = 1; $i <= ceil($total / $limit); $i++): ?>
                    <a href="index.php?controller=establecimiento&page=<?= $i ?>&limit=<?= $limit ?>"
                        class="px-3 py-1 border rounded <?= ($offset / $limit + 1) == $i ? 'bg-[#2563EB] text-white hover:bg-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-300' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/establecimiento_nuevo.js"></script>