<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="w-full px-4 md:px-8 py-8">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-4 rounded-xl shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-black">
                            Detalle del Cliente
                        </h1>
                        <p class="text-slate-600 mt-1 font-medium"><?= htmlspecialchars($cliente['razon_social']) ?></p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="index.php?controller=superadmin&action=clientes"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-slate-600 to-slate-700 text-black font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver a la Lista
                    </a>
                </div>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Información Básica -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 bg-gradient-to-br from-blue-100 to-indigo-200 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800">Información Básica</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 uppercase tracking-wider">Razón Social</label>
                            <p class="text-lg font-medium text-slate-900 bg-slate-50 px-4 py-3 rounded-lg"><?= htmlspecialchars($cliente['razon_social']) ?></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 uppercase tracking-wider">RUC</label>
                            <p class="text-lg font-medium text-slate-900 bg-slate-50 px-4 py-3 rounded-lg"><?= htmlspecialchars($cliente['ruc']) ?></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 uppercase tracking-wider">Correo Electrónico</label>
                            <p class="text-lg font-medium text-slate-900 bg-slate-50 px-4 py-3 rounded-lg"><?= htmlspecialchars($cliente['correo']) ?></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 uppercase tracking-wider">Teléfono</label>
                            <p class="text-lg font-medium text-slate-900 bg-slate-50 px-4 py-3 rounded-lg"><?= htmlspecialchars($cliente['telefono'] ?? 'No especificado') ?></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 uppercase tracking-wider">Distrito</label>
                            <p class="text-lg font-medium text-slate-900 bg-slate-50 px-4 py-3 rounded-lg"><?= htmlspecialchars($cliente['distrito']) ?></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 uppercase tracking-wider">Provincia</label>
                            <p class="text-lg font-medium text-slate-900 bg-slate-50 px-4 py-3 rounded-lg"><?= htmlspecialchars($cliente['provincia']) ?></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 uppercase tracking-wider">Fecha de Registro</label>
                            <p class="text-lg font-medium text-slate-900 bg-slate-50 px-4 py-3 rounded-lg"><?= date('d/m/Y H:i', strtotime($cliente['date_create'])) ?></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 uppercase tracking-wider">Estado</label>
                            <div class="flex items-center">
                                <?php if ($cliente['estado'] == 1): ?>
                                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold bg-gradient-to-r from-green-100 to-emerald-200 text-green-800 border border-green-300">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Cliente Activo
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold bg-gradient-to-r from-red-100 to-pink-200 text-red-800 border border-red-300">
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>Cliente Inactivo
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del Cliente -->
            <div class="space-y-6">
                <!-- Total Establecimientos -->
                <div class="bg-white rounded-2xl p-6 text-white shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-700 text-lg font-bold">Establecimientos</p>
                            <p class="text-black text-lg font-medium"><?= count($establecimientos) ?></p>
                        </div>
                        <div class="p-3 bg-white border-2 border-blue-200 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Usuarios -->
                <div class="bg-white rounded-2xl p-6 text-white shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-700 text-lg font-bold">Usuarios</p>
                            <p class="text-black text-lg font-medium"><?= count($usuarios) ?></p>
                        </div>
                        <div class="p-3 bg-white border-2 border-emerald-200 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Estado General -->
                <div class="bg-white rounded-2xl p-6 text-white shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-700 text-lg font-bold">Tipo</p>
                            <p class="text-black text-lg font-medium">Cliente Premium</p>
                        </div>
                        <div class="p-3 bg-white border-2 border-purple-200 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Establecimientos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-gradient-to-br from-blue-100 to-indigo-200 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">Establecimientos</h2>
                </div>

                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <?php if ($establecimientos): ?>
                        <?php foreach ($establecimientos as $establecimiento): ?>
                            <div class="bg-gradient-to-r from-slate-50 to-blue-50 border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold text-slate-900"><?= htmlspecialchars($establecimiento['etiqueta']) ?></h3>
                                        <p class="text-slate-600 text-sm"><?= htmlspecialchars($establecimiento['direccion'] ?? 'Sin dirección') ?></p>
                                    </div>
                                    <div class="flex items-center">
                                        <?php if ($establecimiento['estado'] == 1): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1"></span>Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No hay establecimientos registrados</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Usuarios -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-gradient-to-br from-green-100 to-emerald-200 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">Usuarios</h2>
                </div>

                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <?php if ($usuarios): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <div class="bg-gradient-to-r from-slate-50 to-green-50 border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-emerald-200 rounded-full flex items-center justify-center">
                                            <span class="text-green-600 font-bold text-sm"><?= strtoupper(substr($usuario['usuario'], 0, 2)) ?></span>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-slate-900"><?= htmlspecialchars($usuario['usuario']) ?></h3>
                                            <p class="text-slate-600 text-sm"><?= htmlspecialchars($usuario['correo']) ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= $usuario['rol'] === 'Administrador' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                            <?= htmlspecialchars($usuario['rol']) ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($usuario['establecimiento_nombre']): ?>
                                    <p class="text-slate-500 text-xs ml-13">📍 <?= htmlspecialchars($usuario['establecimiento_nombre']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No hay usuarios registrados</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>