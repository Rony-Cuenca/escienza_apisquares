<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="w-full bg-white rounded-lg shadow-2xl shadow-blue-300 p-2 md:p-8">
        <!-- Botón Nuevo -->
        <div class="mb-4 flex justify-end">
            <button id="btnNuevoEstablecimiento"
                class="flex items-center gap-2 bg-[#0018F4] hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo
            </button>
        </div>
        <!-- Tabla de Establecimientos -->
        <div class="mb-8 overflow-x-auto rounded-xl border border-[#0018F4]">
            <table class="w-full min-w-max bg-white text-sm">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-1 text-center font-semibold border-b border-[#0018F4] w-[40px]"><strong>#</strong></th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#0018F4] w-[120px]">RUC</th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#0018F4] w-[220px]">Razón Social</th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#0018F4] w-[220px]">Dirección</th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#0018F4] w-[120px]">Estado</th>
                        <th class="py-2 px-1 border-b border-[#0018F4] w-[40px]"><strong></strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($establecimientos)): ?>
                        <?php $i = 1;
                        foreach ($establecimientos as $row): ?>
                            <tr class="border-b border-[#0018F4] hover:bg-blue-50 transition">
                                <td class="py-2 px-1 text-center"><?= $i++ ?></td>
                                <td class="py-2 px-1"><?= htmlspecialchars($row['ruc']) ?></td>
                                <td class="py-2 px-1"><?= htmlspecialchars($row['razon_social']) ?></td>
                                <td class="py-2 px-1"><?= htmlspecialchars($row['direccion']) ?></td>
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
                                        <a href="javascript:void(0);"
                                            data-action="editar"
                                            data-id="<?= $row['id'] ?>"
                                            data-ruc="<?= htmlspecialchars($row['ruc']) ?>"
                                            data-razon_social="<?= htmlspecialchars($row['razon_social']) ?>"
                                            data-direccion="<?= htmlspecialchars($row['direccion']) ?>"
                                            data-estado="<?= $row['estado'] ?>"
                                            class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-black">
                                            Editar
                                        </a>
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
                            <td colspan="6" class="text-center py-4 text-gray-500">No hay establecimientos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Paginación -->
        <div class="flex items-center justify-between bg-white">
            <div class="text-sm text-black">
                Mostrando <?= $offset + 1 ?> a <?= min($offset + $limit, $total) ?> de <?= $total ?> establecimientos
            </div>
            <div class="flex gap-2">
                <?php for ($i = 1; $i <= ceil($total / $limit); $i++): ?>
                    <a href="index.php?controller=establecimiento&page=<?= $i ?>&limit=<?= $limit ?>"
                        class="px-3 py-1 border rounded <?= ($offset / $limit + 1) == $i ? 'bg-[#0018F4] text-white hover:bg-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-300' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Establecimiento -->
<div id="modalEstablecimiento" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6">
        <div class="flex justify-between items-center border-b pb-3">
            <h2 id="modalEstTitulo" class="text-xl font-semibold">Nuevo Establecimiento</h2>
            <button id="btnCerrarModalEst" class="text-gray-500 hover:text-gray-700">
                &times;
            </button>
        </div>
        <form id="formEstablecimiento" method="post" action="index.php?controller=establecimiento&action=crear" autocomplete="off">
            <input type="hidden" name="id" id="modalEstId">
            <input type="hidden" name="estado" id="modalEstEstado">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="modalEstRuc" class="block text-sm font-medium mb-1">RUC</label>
                    <input type="text" name="ruc" id="modalEstRuc" maxlength="11" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label for="modalEstRazon" class="block text-sm font-medium mb-1">Razón Social</label>
                    <input type="text" name="razon_social" id="modalEstRazon" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div class="md:col-span-2">
                    <label for="modalEstDireccion" class="block text-sm font-medium mb-1">Dirección</label>
                    <input type="text" name="direccion" id="modalEstDireccion" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>
            <div class="flex justify-end mt-6 gap-2">
                <button type="button" id="btnCancelarModalEst" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                <button type="submit" class="bg-[#0018F4] hover:bg-blue-700 text-white px-6 py-2 rounded">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/establecimiento.js"></script>