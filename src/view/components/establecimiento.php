<?php
require_once __DIR__ . '/../../helpers/permisos_helper.php';
?>
<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="w-full bg-white rounded-lg shadow-2xl shadow-gray-300/40 p-2 md:p-8">
        <!-- Cabecera con título y botones -->
        <div class="flex items-center justify-between w-full pb-6 px-6 border-b border-gray-200 mb-8">
            <div class="flex flex-col">
                <span class="text-xl text-gray-800 font-semibold" style="font-family: 'Montserrat', sans-serif;">LISTA DE ESTABLECIMIENTOS</span>
                <?php if (!empty($cliente)): ?>
                    <span class="text-sm text-gray-600 mt-1" style="font-family: 'Montserrat', sans-serif;">
                        <?= htmlspecialchars($cliente['razon_social']) ?> - <?= htmlspecialchars($cliente['ruc']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="flex flex-col-2 items-end gap-2">
                <?php if (puedeCrearEstablecimientos()): ?>
                    <button id="btnNuevoEstablecimiento"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-normal py-2 px-4 rounded-lg shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo
                    </button>
                <?php endif; ?>
                <?php if (puedeSincronizarEstablecimientos()): ?>
                    <button id="btnSincronizar"
                        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-normal py-2 px-4 rounded-lg shadow hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sincronizar
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <!-- Tabla de Establecimientos -->
        <div class="mb-8 overflow-x-auto rounded-xl border border-[#2563EB]" style="font-family: 'Poppins', sans-serif; font-weight: 300;">
            <table class="w-full min-w-max bg-white text-sm">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-1 text-center font-semibold border-b border-[#2563EB] w-[40px]"><strong>#</strong></th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[120px]">
                            <a href="index.php?controller=establecimiento&sort=tipo_establecimiento&dir=<?= $sort === 'tipo_establecimiento' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>TIPO</strong></a>
                        </th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[280px]">
                            <a href="index.php?controller=establecimiento&sort=etiqueta&dir=<?= $sort === 'etiqueta' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ETIQUETA</strong></a>
                        </th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[300px]">
                            <a href="index.php?controller=establecimiento&sort=direccion&dir=<?= $sort === 'direccion' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>DIRECCIÓN</strong></a>
                        </th>
                        <th class="py-2 px-1 text-center font-semibold border-b border-[#2563EB] w-[60px]">
                            <a href="index.php?controller=establecimiento&sort=origen&dir=<?= $sort === 'origen' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ORIGEN</strong></a>
                        </th>
                        <th class="py-2 px-1 text-center font-semibold border-b border-[#2563EB] w-[80px]">
                            <a href="index.php?controller=establecimiento&sort=estado&dir=<?= $sort === 'estado' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ESTADO</strong></a>
                        </th>
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
                                    <span class="text-xs <?= $row['tipo_establecimiento'] === 'MATRIZ' ? 'text-blue-600 font-semibold' : 'text-gray-600' ?>">
                                        <?= htmlspecialchars($row['tipo_establecimiento'] ?? 'MATRIZ') ?>
                                    </span>
                                </td>
                                <td class="py-2 px-1">
                                    <?= htmlspecialchars($row['etiqueta'] ?? $cliente['razon_social'] ?? 'Sin etiqueta') ?>
                                </td>
                                <td class="py-2 px-1"><?= !empty($row['direccion']) ? htmlspecialchars($row['direccion']) : 'Sin establecer' ?></td>
                                <td class="py-2 px-1 text-center">
                                    <?php if (($row['origen'] ?? 'SUNAT') === 'MANUAL'): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Manual
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            SUNAT
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-1 text-center">
                                    <?php if (puedeCambiarEstadoEstablecimientos()): ?>
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
                                                <span class="w-2 h-2 bg-gray-500 rounded-full mr-2"></span>Deshabilitado
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($row['estado'] == 1): ?>
                                            <span class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-green-200 text-green-800 text-sm font-medium">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Activo
                                            </span>
                                        <?php elseif ($row['estado'] == 2): ?>
                                            <span class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-red-200 text-yellow-800 text-sm font-medium">
                                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>Inactivo
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm font-medium opacity-90">
                                                <span class="w-2 h-2 bg-gray-500 rounded-full mr-2"></span>Deshabilitado
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-1 text-center relative">
                                    <?php if (puedeEditarEstablecimientos()): ?>
                                        <div class="relative inline-block text-left">
                                            <button data-action="toggleMenu" type="button" class="focus:outline-none" aria-label="Abrir menú de establecimiento">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="5" r="2" fill="#000" />
                                                    <circle cx="12" cy="12" r="2" fill="#000" />
                                                    <circle cx="12" cy="19" r="2" fill="#000" />
                                                </svg>
                                            </button>
                                            <div id="menuEstablecimiento" class="hidden absolute right-0 z-50 w-32 bg-white rounded-lg shadow-lg border min-w-[120px]">
                                                <a href="javascript:void(0);" data-action="editarEstablecimiento" data-id="<?= $row['id'] ?>"
                                                    class="block w-full text-left px-4 py-2 text-blue-600 hover:bg-gray-100">Editar</a>
                                                <?php if (puedeCambiarEstadoEstablecimientos()): ?>
                                                    <?php if ($row['estado'] != 3): ?>
                                                        <a href="javascript:void(0);" data-action="cambiarEstado" data-id="<?= $row['id'] ?>" data-estado="3"
                                                            class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">Deshabilitar</a>
                                                    <?php else: ?>
                                                        <a href="javascript:void(0);" data-action="cambiarEstado" data-id="<?= $row['id'] ?>" data-estado="1"
                                                            class="block w-full text-left px-4 py-2 text-green-600 hover:bg-gray-100">Habilitar</a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
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
                    <a href="index.php?controller=establecimiento&page=<?= $i ?>&limit=<?= $limit ?>&sort=<?= htmlspecialchars($sort ?? 'codigo_establecimiento') ?>&dir=<?= htmlspecialchars($dir ?? 'ASC') ?>"
                        class="px-3 py-1 border rounded <?= ($offset / $limit + 1) == $i ? 'bg-[#2563EB] text-white hover:bg-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-300' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edición -->
<div id="modalEditarEstablecimiento" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Editar Establecimiento</h3>
            </div>
            <form id="formEditarEstablecimiento">
                <div class="px-6 py-4 space-y-4">
                    <input type="hidden" id="editId" name="id">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Etiqueta / Apodo
                        </label>
                        <input type="text"
                            id="editEtiqueta"
                            name="etiqueta"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ej: Grifo Principal, Sucursal Centro, etc.">
                        <p class="text-xs text-gray-500 mt-1">Este nombre se mostrará en la tabla. Si se deja vacío, se usará la razón social.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Dirección
                        </label>
                        <textarea id="editDireccion"
                            name="direccion"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ingrese la dirección del establecimiento"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button"
                        id="btnCancelarEdicion"
                        class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Nuevo Establecimiento -->
<div id="modalNuevoEstablecimiento" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Nuevo Establecimiento</h3>
                <p class="text-sm text-gray-600 mt-1">Crea un establecimiento que no aparece en SUNAT</p>
            </div>
            <form id="formNuevoEstablecimiento">
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Código Establecimiento *
                            </label>
                            <input type="text"
                                id="nuevoCodigo"
                                name="codigo_establecimiento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                maxlength="10"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo Establecimiento *
                            </label>
                            <select id="nuevoTipo"
                                name="tipo_establecimiento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                                <option value="">Seleccionar...</option>
                                <option value="MATRIZ">MATRIZ</option>
                                <option value="SU. SUCURSAL">SU. SUCURSAL</option>
                                <option value="LO. L. COMERCIAL">LO. L. COMERCIAL</option>
                                <option value="DE. DEPOSITO">DE. DEPOSITO</option>
                                <option value="OF. OFICINA">OF. OFICINA</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Etiqueta / Apodo *
                        </label>
                        <input type="text"
                            id="nuevaEtiqueta"
                            name="etiqueta"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Dirección *
                        </label>
                        <textarea id="nuevaDireccion"
                            name="direccion"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required></textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Departamento
                            </label>
                            <input type="text"
                                id="nuevoDepartamento"
                                name="departamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Provincia
                            </label>
                            <input type="text"
                                id="nuevaProvincia"
                                name="provincia"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Distrito
                            </label>
                            <input type="text"
                                id="nuevoDistrito"
                                name="distrito"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button"
                        id="btnCancelarNuevo"
                        class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                        Crear Establecimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/establecimiento.js"></script>