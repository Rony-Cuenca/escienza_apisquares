<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="w-full px-4 md:px-8 py-8">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-br from-blue-600 to-purple-600 p-4 rounded-xl shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-black">
                            Panel Escienza
                        </h1>
                        <p class="text-slate-600 mt-1 font-medium">Gestión completa del sistema</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Card Total Clientes -->
            <div class="group relative overflow-hidden bg-white border-2 border-blue-500 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white border-2 border-blue-200 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-slate-600 text-sm font-medium">Total Clientes</p>
                            <p class="text-4xl font-bold text-black"><?= $totalClientes ?></p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-blue-200">
                        <p class="text-slate-600 text-sm">📈 Activos en el sistema</p>
                    </div>
                </div>
            </div>

            <!-- Card Total Usuarios -->
            <div class="group relative overflow-hidden bg-white border-2 border-green-500 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white border-2 border-emerald-200 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-slate-600 text-sm font-medium">Total Usuarios</p>
                            <p class="text-4xl font-bold text-black"><?= $totalUsuarios ?></p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-emerald-200">
                        <p class="text-slate-600 text-sm">👥 Usuarios registrados</p>
                    </div>
                </div>
            </div>

            <!-- Card Sistema -->
            <div class="group relative overflow-hidden bg-white border-2 border-purple-500 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white border-2 border-purple-200 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-slate-600 text-sm font-medium">Sistema</p>
                            <p class="text-xl font-bold text-black">ESCIENZA API</p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-purple-200">
                        <p class="text-slate-600 text-sm">🚀 Operativo y seguro</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8 mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-gradient-to-br from-orange-400 to-pink-500 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800">Acciones Rápidas</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Gestionar Clientes -->
                <a href="index.php?controller=superadmin&action=clientes"
                    class="group relative overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 hover:border-blue-400 rounded-2xl p-6 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:shadow-xl transition-shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-slate-800 text-lg group-hover:text-blue-600 transition-colors">Gestionar Clientes</h3>
                            <p class="text-slate-600 text-sm mt-1">Ver y administrar todos los clientes del sistema</p>
                            <div class="mt-3 flex items-center text-blue-600 text-sm font-medium">
                                <span>Acceder ahora</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -top-2 -right-2 w-20 h-20 bg-gradient-to-br from-blue-200/20 to-transparent rounded-full blur-2xl"></div>
                </a>

                <!-- Reportes Globales -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-gray-50 to-slate-100 border-2 border-gray-200 rounded-2xl p-6 opacity-70">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-gradient-to-br from-gray-300 to-gray-400 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-600 text-lg">Reportes Globales</h3>
                            <p class="text-gray-500 text-sm mt-1">Análisis completo de datos del sistema</p>
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">
                                🚧 Próximamente
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración Global -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-gray-50 to-slate-100 border-2 border-gray-200 rounded-2xl p-6 opacity-70">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-gradient-to-br from-gray-300 to-gray-400 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-600 text-lg">Configuración Global</h3>
                            <p class="text-gray-500 text-sm mt-1">Ajustes avanzados del sistema</p>
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">
                                🚧 Próximamente
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes Recientes -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-teal-400 to-cyan-500 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">Clientes Recientes</h2>
                </div>
                <a href="index.php?controller=superadmin&action=clientes"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-black rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <span class="font-medium">Ver todos</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200">
                <table class="w-full table-fixed">
                    <thead class="bg-gradient-to-r from-slate-200 to-slate-300 border-b border-slate-300">
                        <tr>
                            <th class="w-2/5 px-6 py-4 text-left text-xs font-bold text-slate-800 uppercase tracking-wider">Cliente</th>
                            <th class="w-1/6 px-6 py-4 text-left text-xs font-bold text-slate-800 uppercase tracking-wider">RUC</th>
                            <th class="w-1/5 px-6 py-4 text-left text-xs font-bold text-slate-800 uppercase tracking-wider">Ubicación</th>
                            <th class="w-1/6 px-6 py-4 text-left text-xs font-bold text-slate-800 uppercase tracking-wider">Fecha Registro</th>
                            <th class="w-1/6 px-6 py-4 text-center text-xs font-bold text-slate-800 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <?php if ($clientesRecientes): ?>
                            <?php foreach ($clientesRecientes as $cliente): ?>
                                <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-bold text-slate-900 truncate" title="<?= htmlspecialchars($cliente['razon_social']) ?>">
                                                    <?= htmlspecialchars($cliente['razon_social']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 w-full justify-center">
                                            <?= htmlspecialchars($cliente['ruc']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1 text-sm text-slate-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="truncate" title="<?= htmlspecialchars($cliente['distrito'] . ' - ' . $cliente['provincia']) ?>">
                                                <?= htmlspecialchars($cliente['distrito'] . ' - ' . $cliente['provincia']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1 text-sm text-slate-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span><?= date('d/m/Y', strtotime($cliente['date_create'])) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="index.php?controller=superadmin&action=verCliente&id=<?= $cliente['id'] ?>"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Ver detalles del cliente">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span class="hidden lg:inline">Ver</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
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
    </div>
</div>