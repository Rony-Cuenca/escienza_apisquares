<?php
$id = $_GET['id'] ?? null;
if (!$id || !$cliente) {
    header('Location: ?controller=superadmin&action=clientes');
    exit;
}
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4">
                <a href="?controller=superadmin&action=clientes"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Clientes
                </a>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mt-4">Detalle del Cliente</h1>
            <p class="text-gray-600 mt-2"><?= htmlspecialchars($cliente['razon_social']) ?></p>
        </div>

        <!-- Información del Cliente -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
            <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                <h2 class="text-lg font-semibold text-blue-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Información General
                </h2>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RUC</label>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg text-gray-900 font-mono">
                                <?= htmlspecialchars($cliente['ruc']) ?>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Razón Social</label>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg text-gray-900">
                                <?= htmlspecialchars($cliente['razon_social']) ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($cliente['correo'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg text-gray-900">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                    <?= htmlspecialchars($cliente['correo']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($cliente['telefono'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg text-gray-900">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <?= htmlspecialchars($cliente['telefono']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($cliente['direccion'])): ?>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg text-gray-900">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <?= htmlspecialchars($cliente['direccion']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Registro</label>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg text-gray-900">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <?= date('d/m/Y', strtotime($cliente['date_create'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Establecimientos -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-green-50 border-b border-green-100">
                <h2 class="text-lg font-semibold text-green-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Establecimientos Asociados
                </h2>
            </div>

            <div class="p-6">
                <?php
                require_once 'model/Establecimiento.php';
                $establecimientos = Establecimiento::obtenerPorCliente($cliente['id'], 10, 0);
                ?>

                <?php if (!empty($establecimientos)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etiqueta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($establecimientos as $establecimiento): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($establecimiento['codigo_establecimiento']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($establecimiento['tipo_establecimiento']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($establecimiento['etiqueta'] ?? 'Sin etiqueta') ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($establecimiento['direccion']) ?>">
                                            <?= htmlspecialchars($establecimiento['direccion']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $estado_texto = 'Desconocido';
                                            $estado_clase = 'bg-gray-100 text-gray-800';

                                            switch ($establecimiento['estado']) {
                                                case 1:
                                                    $estado_texto = 'Activo';
                                                    $estado_clase = 'bg-green-100 text-green-800';
                                                    break;
                                                case 2:
                                                    $estado_texto = 'Inactivo';
                                                    $estado_clase = 'bg-red-100 text-red-800';
                                                    break;
                                                case 3:
                                                    $estado_texto = 'Pendiente';
                                                    $estado_clase = 'bg-yellow-100 text-yellow-800';
                                                    break;
                                            }
                                            ?>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $estado_clase ?>">
                                                <?= $estado_texto ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay establecimientos</h3>
                        <p class="mt-1 text-sm text-gray-500">Este cliente aún no tiene establecimientos registrados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>