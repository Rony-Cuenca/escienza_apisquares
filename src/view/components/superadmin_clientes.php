<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="w-full px-4 md:px-8 py-8">
        <!-- Header mejorado -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-4 rounded-xl shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-black">
                            Gestión de Clientes
                        </h1>
                        <p class="text-slate-600 mt-1 font-medium">Administración completa de todos los clientes del sistema</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Clientes mejorada -->
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
                            foreach ($clientes as $cliente): ?>
                                <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                                    <td class="py-4 px-6 text-center text-slate-600 font-medium"><?= $i++ ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-indigo-200 rounded-full flex items-center justify-center">
                                                <span class="text-blue-600 font-bold text-sm"><?= strtoupper(substr($cliente['razon_social'], 0, 2)) ?></span>
                                            </div>
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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <?= htmlspecialchars($cliente['distrito'] . ' - ' . $cliente['provincia']) ?>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($cliente['estado'] == 1): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-green-100 to-emerald-200 text-green-800 border border-green-300">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-100 to-pink-200 text-red-800 border border-red-300">
                                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-2">
                                            <a href="index.php?controller=superadmin&action=verCliente&id=<?= $cliente['id'] ?>"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                                                title="Ver detalles del cliente">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Ver
                                            </a>
                                            <a href="index.php?controller=superadmin&action=impersonarCliente&id=<?= $cliente['id'] ?>"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                                                title="Entrar como este cliente"
                                                onclick="return confirm('¿Estás seguro de querer entrar como este cliente?')">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Entrar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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

        <!-- Paginación mejorada -->
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
                                            ? 'px-4 py-2 bg-blue-600 font-bold rounded-xl shadow-lg text-white'
                                            : 'px-4 py-2 font-medium rounded-xl hover:from-blue-100 hover:to-indigo-100 hover:text-blue-600 transition-all duration-200' ?> 
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