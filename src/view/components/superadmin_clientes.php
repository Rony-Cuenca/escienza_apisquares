<?php
$errores = $_SESSION['errores'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['errores'], $_SESSION['form_data']);
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="w-full px-4 md:px-8 py-8">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-4 sm:p-8 mb-8">
            <div class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-3 sm:p-4 rounded-xl shadow-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 sm:w-8 sm:h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-3xl font-bold text-black break-words">
                            Gestión de Clientes
                        </h1>
                        <p class="text-slate-600 mt-1 font-medium text-sm sm:text-base">Administración completa de todos los clientes del sistema</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto justify-end">
                    <button onclick="abrirModalCliente()"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-black px-4 sm:px-6 py-2 sm:py-3 rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 text-sm sm:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span class="xs:inline">Agregar Cliente</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="relative w-full max-w-md mb-8">
            <input
                type="text"
                id="busquedaCliente"
                placeholder="Buscar por RUC o Razón Social..."
                autocomplete="off"
                class="w-full px-4 sm:px-5 py-2 sm:py-3 pl-10 sm:pl-12 rounded-xl border border-slate-300 bg-gradient-to-r from-slate-50 to-blue-50 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 font-medium transition-all duration-200 text-sm sm:text-base">
            <span class="absolute left-2 sm:left-3 top-0 h-full flex items-center text-blue-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" fill="none" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </span>
        </div>
        <!-- Mensajes de éxito/error -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 mb-6">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex-shrink-0 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium text-green-800 break-words">
                            <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4 mb-6">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex-shrink-0 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium text-red-800 break-words">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Tabla de Clientes -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-slate-200 to-slate-300 border-b border-slate-400">
                        <tr>
                            <th class="py-4 px-6 text-center text-slate-800 font-bold text-sm uppercase tracking-wider">#</th>
                            <th class="py-4 px-6 text-left text-slate-800 font-bold text-sm uppercase tracking-wider">
                                <a href="index.php?controller=superadmin&action=clientes&sort=razon_social&dir=<?= $sort === 'razon_social' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>"
                                    class="flex items-center gap-2 hover:text-blue-600 transition-colors">
                                    <span>Razón Social</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                </a>
                            </th>
                            <th class="py-4 px-6 text-left text-slate-800 font-bold text-sm uppercase tracking-wider">
                                <a href="index.php?controller=superadmin&action=clientes&sort=ruc&dir=<?= $sort === 'ruc' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>"
                                    class="flex items-center gap-2 hover:text-blue-600 transition-colors">
                                    <span>RUC</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                </a>
                            </th>
                            <th class="py-4 px-6 text-left text-slate-800 font-bold text-sm uppercase tracking-wider">Ubicación</th>
                            <th class="py-4 px-6 text-left text-slate-800 font-bold text-sm uppercase tracking-wider">
                                <a href="index.php?controller=superadmin&action=clientes&sort=estado&dir=<?= $sort === 'estado' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>"
                                    class="flex items-center gap-2 hover:text-blue-600 transition-colors">
                                    <span>Estado</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                </a>
                            </th>
                            <th class="py-4 px-6 text-center text-slate-800 font-bold text-sm uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <?php if ($clientes): ?>
                            <?php $i = ($page - 1) * $limit + 1;
                            foreach ($clientes as $cliente) {
                                if ($cliente['id'] == 1) continue;
                            ?>
                                <!-- Fila principal del cliente -->
                                <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                                    <td class="py-4 px-6 text-center text-slate-600 font-medium">
                                        <?= $i++ ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="font-semibold text-slate-900"><?= htmlspecialchars($cliente['razon_social']) ?></div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <?= htmlspecialchars($cliente['ruc']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-1 text-slate-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="truncate" title="<?= htmlspecialchars($cliente['distrito'] . ' - ' . $cliente['provincia']) ?>">
                                                <?= htmlspecialchars($cliente['distrito'] . ' - ' . $cliente['provincia']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-left align-middle">
                                        <?php if ($cliente['estado'] == 1): ?>
                                            <button onclick="cambiarEstadoCliente(<?= $cliente['id'] ?>, 2)"
                                                class="inline-flex items-center min-w-[120px] justify-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-green-100 to-emerald-200 text-green-800 border border-green-300 hover:from-green-200 hover:to-emerald-300 transition-all duration-200 cursor-pointer"
                                                title="Hacer clic para desactivar">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Activo
                                            </button>
                                        <?php elseif ($cliente['estado'] == 2): ?>
                                            <button onclick="cambiarEstadoCliente(<?= $cliente['id'] ?>, 1)"
                                                class="inline-flex items-center min-w-[120px] justify-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-100 to-pink-200 text-red-800 border border-red-300 hover:from-red-200 hover:to-pink-300 transition-all duration-200 cursor-pointer"
                                                title="Hacer clic para activar">
                                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>Inactivo
                                            </button>
                                        <?php else: ?>
                                            <span class="inline-flex items-center min-w-[120px] justify-center px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-500 border border-gray-300 cursor-not-allowed select-none" title="Cliente deshabilitado">
                                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>Deshabilitado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php
                                        $establecimiento_principal = null;
                                        if (!empty($cliente['establecimientos'])) {
                                            foreach ($cliente['establecimientos'] as $est) {
                                                if ($est['codigo_establecimiento'] === '0000') {
                                                    $establecimiento_principal = $est;
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        <div class="grid grid-cols-2 gap-2">
                                            <!-- Fila 1: Ver | Ingresar -->
                                            <div>
                                                <a href="index.php?controller=superadmin&action=verCliente&id=<?= $cliente['id'] ?>"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                                    title="Ver detalles del cliente">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                            </div>
                                            <div>
                                                <?php if ($establecimiento_principal && $establecimiento_principal['estado'] != 3): ?>
                                                    <button type="button"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                                        title="Ingresar al establecimiento principal"
                                                        onclick="entrarEstablecimientoSweet(<?= $cliente['id'] ?>, <?= $establecimiento_principal['id'] ?>)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                        </svg>
                                                    </button>
                                                <?php elseif ($establecimiento_principal && $establecimiento_principal['estado'] == 3): ?>
                                                    <button type="button" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-400 text-white text-xs font-medium rounded-lg w-full justify-center cursor-not-allowed" title="Establecimiento deshabilitado" disabled>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                        </svg>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-400 text-white text-xs font-medium rounded-lg w-full justify-center cursor-not-allowed" title="No hay un establecimiento" disabled>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                        </svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Fila 2: Editar | Deshabilitar/Habilitar Cliente -->
                                            <div>
                                                <button onclick="abrirModalEdicion(<?= $cliente['id'] ?>)"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                                    title="Editar cliente"
                                                    <?= $cliente['estado'] == 3 ? 'disabled class="opacity-50 cursor-not-allowed"' : '' ?>>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div>
                                            <?php if ($cliente['estado'] != 3): ?>
                                                <button type="button"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-700 hover:bg-gray-800 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                                    title="Deshabilitar cliente"
                                                    onclick="cambiarEstadoCliente(<?= $cliente['id'] ?>, 3)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 6L6 18" />
                                                        <circle cx="12" cy="12" r="9" />
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <button type="button"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full justify-center"
                                                    title="Habilitar cliente"
                                                    onclick="cambiarEstadoCliente(<?= $cliente['id'] ?>, 1)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <div class="text-gray-500 font-medium">No hay clientes registrados</div>
                                        <div class="text-gray-400 text-sm">Los clientes aparecerán aquí cuando se registren</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-gradient-to-br from-slate-100 to-slate-200 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="text-slate-700 font-medium">
                        Mostrando <span class="font-bold text-blue-600"><?= $offset + 1 ?></span> a <span class="font-bold text-blue-600"><?= min($offset + $limit, $total) ?></span> de <span class="font-bold text-blue-600"><?= $total ?></span> clientes
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <?php if (ceil($total / $limit) > 1): ?>
                        <?php for ($i = 1; $i <= ceil($total / $limit); $i++): ?>
                            <a href="index.php?controller=superadmin&action=clientes&page=<?= $i ?>&limit=<?= $limit ?>"
                                class="<?= $i == $page
                                            ? 'px-4 bg-blue-600 rounded-xl hover:bg-blue-700 shadow-lg text-white'
                                            : 'px-4 font-medium rounded-xl hover:bg-gray-200 hover:text-blue-500 transition-all duration-200' ?> 
                                inline-flex items-center justify-center min-w-[40px] h-10">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Cliente -->
<div id="modalCliente" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <!-- Header del Modal -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Agregar Nuevo Cliente</h2>
                            <p class="text-gray-600 text-sm">Complete la información del cliente</p>
                        </div>
                    </div>
                    <button onclick="cerrarModalCliente()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contenido del Modal -->
            <div class="p-6">
                <!-- Mensajes de error -->
                <div id="mensajesModal" class="hidden mb-4"></div>

                <!-- Formulario -->
                <form id="formCliente" method="POST" action="?controller=cliente&action=crear" class="space-y-6">
                    <!-- RUC y Razón Social -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="ruc" class="block text-sm font-medium text-gray-700 mb-2">
                                RUC <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text"
                                    id="ruc"
                                    name="ruc"
                                    value="<?= htmlspecialchars($form_data['ruc'] ?? '') ?>"
                                    maxlength="11"
                                    pattern="[0-9]{11}"
                                    placeholder="Ingrese el RUC (11 dígitos)"
                                    required
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="button"
                                    id="btnConsultarRuc"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Haga clic en 🔍 para consultar y autocompletar datos básicos</p>
                        </div>

                        <div>
                            <label for="razon_social" class="block text-sm font-medium text-gray-700 mb-2">
                                Razón Social <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                id="razon_social"
                                name="razon_social"
                                value="<?= htmlspecialchars($form_data['razon_social'] ?? '') ?>"
                                placeholder="Ingrese la razón social"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Email y Teléfono -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>
                            <input type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                placeholder="cliente@empresa.com"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono
                            </label>
                            <input type="tel"
                                id="telefono"
                                name="telefono"
                                maxlength="9"
                                pattern="[0-9]{9}"
                                value="<?= htmlspecialchars($form_data['telefono'] ?? '') ?>"
                                placeholder="999 999 999"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">
                            Dirección
                        </label>
                        <textarea id="direccion"
                            name="direccion"
                            rows="3"
                            placeholder="Ingrese la dirección del cliente"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($form_data['direccion'] ?? '') ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Esta dirección también se usará para el establecimiento principal</p>
                    </div>

                    <!-- Ubicación -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="departamento" class="block text-sm font-medium text-gray-700 mb-2">
                                Departamento
                            </label>
                            <input type="text"
                                id="departamento"
                                name="departamento"
                                value="<?= htmlspecialchars($form_data['departamento'] ?? '') ?>"
                                placeholder="Departamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="provincia" class="block text-sm font-medium text-gray-700 mb-2">
                                Provincia
                            </label>
                            <input type="text"
                                id="provincia"
                                name="provincia"
                                placeholder="Provincia"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="distrito" class="block text-sm font-medium text-gray-700 mb-2">
                                Distrito
                            </label>
                            <input type="text"
                                id="distrito"
                                name="distrito"
                                placeholder="Distrito"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div id="infoEstablecimientos" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m-1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Información importante</h3>
                                <p class="mt-1 text-sm text-blue-700">
                                    • Use el botón 🔍 para consultar RUC y autocompletar datos básicos<br>
                                    • Se creará automáticamente un <strong>establecimiento principal</strong> con código "0000"<br>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button type="button"
                            onclick="cerrarModalCliente()"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Cliente -->
<div id="modalEdicion" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <!-- Header del Modal -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Editar Cliente</h2>
                            <p class="text-gray-600 text-sm">Modifique la información del cliente</p>
                        </div>
                    </div>
                    <button onclick="cerrarModalEdicion()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contenido del Modal -->
            <div class="px-6 py-4">
                <!-- Mensajes -->
                <div id="mensajesModalEdicion" class="hidden"></div>

                <!-- Formulario -->
                <form id="formEdicion" method="POST" action="index.php?controller=cliente&action=editar">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- RUC -->
                        <div>
                            <label for="edit_ruc" class="block text-sm font-medium text-gray-700 mb-2">RUC *</label>
                            <input type="text"
                                id="edit_ruc"
                                name="ruc"
                                maxlength="11"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>

                        <!-- Razón Social -->
                        <div>
                            <label for="edit_razon_social" class="block text-sm font-medium text-gray-700 mb-2">Razón Social *</label>
                            <input type="text"
                                id="edit_razon_social"
                                name="razon_social"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email"
                                id="edit_email"
                                name="email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="edit_telefono" class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                            <input type="tel"
                                id="edit_telefono"
                                name="telefono"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>

                        <!-- Dirección -->
                        <div class="md:col-span-2">
                            <label for="edit_direccion" class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                            <input type="text"
                                id="edit_direccion"
                                name="direccion"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>

                        <!-- Ubicación: Departamento, Provincia, Distrito -->
                        <div class="md:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Departamento -->
                                <div>
                                    <label for="edit_departamento" class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
                                    <input type="text"
                                        id="edit_departamento"
                                        name="departamento"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                </div>

                                <!-- Provincia -->
                                <div>
                                    <label for="edit_provincia" class="block text-sm font-medium text-gray-700 mb-2">Provincia</label>
                                    <input type="text"
                                        id="edit_provincia"
                                        name="provincia"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                </div>

                                <!-- Distrito -->
                                <div>
                                    <label for="edit_distrito" class="block text-sm font-medium text-gray-700 mb-2">Distrito</label>
                                    <input type="text"
                                        id="edit_distrito"
                                        name="distrito"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                        <button type="button"
                            onclick="cerrarModalEdicion()"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 font-medium">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/superadmin.js"></script>