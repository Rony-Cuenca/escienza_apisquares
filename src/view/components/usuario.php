<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="w-full bg-white rounded-lg shadow-2xl shadow-gray-300/40 p-2 md:p-8">
        <!-- Cabecera con título y botones -->
        <div class="flex items-center justify-between w-full pb-6 px-6 border-b border-gray-200 mb-8">
            <span class="text-xl text-gray-800 font-semibold" style="font-family: 'Montserrat', sans-serif;">LISTA DE USUARIOS</span>
            <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                <div class="flex gap-2">
                    <button id="btnNuevoUsuario"
                        class="flex items-center gap-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-normal py-1.5 px-3 md:py-2 md:px-4 rounded-lg shadow text-sm md:text-base"
                        data-correo-cliente="<?= htmlspecialchars($correo_cliente) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo
                    </button>
                    <button id="btnGenerarCodigo" class="bg-green-600 hover:bg-green-700 text-white font-normal py-1.5 px-3 md:py-2 md:px-4 rounded-lg shadow flex items-center gap-2 text-sm md:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 010 5.656l-3.535 3.535a4 4 0 01-5.657-5.657l2.121-2.121m6.364-6.364a4 4 0 015.657 5.657l-2.121 2.121" />
                        </svg>
                        Generar Código de Acceso
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <!-- Tabla de Usuarios -->
        <div class="mb-8 overflow-x-auto rounded-xl border border-[#2563EB]" style="font-family: 'Poppins', sans-serif; font-weight: 300;">
            <table class="w-full min-w-max bg-white text-sm">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-1 text-center font-semibold border-b border-[#2563EB] w-[40px]"><strong>#</strong></th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[120px]">
                            <a href="index.php?controller=usuario&sort=usuario&dir=<?= $sort === 'usuario' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>USUARIO</strong></a>
                        </th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[180px]">
                            <a href="index.php?controller=usuario&sort=correo&dir=<?= $sort === 'correo' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>CORREO</strong></a>
                        </th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[120px]">
                            <a href="index.php?controller=usuario&sort=rol&dir=<?= $sort === 'rol' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ROL</strong></a>
                        </th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[220px]">
                            <a href="index.php?controller=usuario&sort=establecimiento&dir=<?= $sort === 'establecimiento' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ESTABLECIMIENTO</strong></a>
                        </th>
                        <th class="py-2 px-1 text-left font-semibold border-b border-[#2563EB] w-[120px]">
                            <a href="index.php?controller=usuario&sort=estado&dir=<?= $sort === 'estado' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ESTADO</strong></a>
                        </th>
                        <th class="py-2 px-1 border-b border-[#2563EB] w-[40px]"><strong></strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($usuarios): ?>
                        <?php $i = 1;
                        foreach ($usuarios as $row): ?>
                            <tr class="border-b border-[#2563EB] hover:bg-blue-50 transition" style="font-size: small;">
                                <td class="py-2 px-1 text-center"><?= $i++ ?></td>
                                <td class="py-2 px-1"><?= htmlspecialchars($row['usuario']) ?></td>
                                <td class="py-2 px-1"><?= !empty($row['correo']) ? htmlspecialchars($row['correo']) : 'Sin establecer' ?></td>
                                <td class="py-2 px-1"><?= htmlspecialchars($row['rol']) ?></td>
                                <td class="py-2 px-1"><?= htmlspecialchars($row['establecimiento']) ?></td>
                                <td class="py-2 px-1">
                                    <?php if ($_SESSION['rol'] === 'Administrador'): ?>
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
                                                Deshabilitado
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-1 text-center relative">
                                    <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                                        <div class="relative inline-block text-left">
                                            <button data-action="toggleMenu" type="button" class="focus:outline-none" aria-label="Abrir menú de usuario">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="5" r="2" fill="#000" />
                                                    <circle cx="12" cy="12" r="2" fill="#000" />
                                                    <circle cx="12" cy="19" r="2" fill="#000" />
                                                </svg>
                                            </button>
                                            <div id="menuUsuario" class="hidden absolute right-0 z-50 w-32 bg-white rounded-lg shadow-lg border min-w-[120px]">
                                                <a href="javascript:void(0);"
                                                    data-action="editar"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-usuario="<?= htmlspecialchars($row['usuario']) ?>"
                                                    data-correo="<?= htmlspecialchars($row['correo']) ?>"
                                                    data-rol="<?= htmlspecialchars($row['rol']) ?>"
                                                    data-establecimiento="<?= $row['id_establecimiento'] ?>"
                                                    data-estado="<?= $row['estado'] ?>"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-black">
                                                    Editar
                                                </a>
                                                <?php if ($row['id'] != $_SESSION['id_usuario']): ?>
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
                            <td colspan="7" class="py-4 px-2 text-center text-gray-500">No hay usuarios para este cliente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Paginación -->
        <div class="flex items-center justify-between bg-white" style="font-family: 'Montserrat', sans-serif; font-weight: lighter;">
            <div class="text-sm text-black">
                Mostrando <?= $offset + 1 ?> a <?= min($offset + $limit, $total) ?> de <?= $total ?> usuarios
            </div>
            <div class="flex gap-2">
                <?php for ($i = 1; $i <= ceil($total / $limit); $i++): ?>
                    <a href="index.php?controller=usuario&page=<?= $i ?>&limit=<?= $limit ?>"
                        class="px-3 py-1 border rounded <?= $i == $page ? 'bg-[#2563EB] text-white hover:bg-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-300' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Usuario Nuevo / Editar -->
<div id="modalUsuario" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6">
        <div class="flex justify-between items-center border-b pb-3">
            <h2 id="modalTitulo" class="text-xl font-semibold">Nuevo Usuario</h2>
            <button id="btnCerrarModal" class="text-gray-500 hover:text-gray-700">
                &times;
            </button>
        </div>
        <form id="formUsuario" method="post" action="index.php?controller=usuario&action=crear" autocomplete="off">
            <input type="hidden" name="id_usuario" id="modalIdUsuario">
            <input type="hidden" name="estado" id="modalUsuarioEstado">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Nombre de usuario</label>
                    <input type="text" name="usuario" id="modalUsuarioNombre" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    <div id="errorUsuario" class="text-red-600 text-sm mb-1 hidden pt-1">
                        <!-- El icono y mensaje se insertan por JS -->
                    </div>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Correo electrónico</label>
                    <input type="email" name="correo" id="modalUsuarioCorreo" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-colors" />
                    <div id="errorCorreo" class="text-red-600 text-sm mb-1 hidden pt-1">
                        <!-- El icono y mensaje se insertan por JS -->
                    </div>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Rol</label>
                    <select name="rol" id="modalUsuarioRol" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Selecciona un rol</option>
                        <?php if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']): ?>
                            <option value="SuperAdmin">Super Administrador</option>
                        <?php endif; ?>
                        <option value="Administrador">Administrador</option>
                        <option value="Contador">Contador</option>
                        <option value="Vendedor">Vendedor</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Establecimiento</label>
                    <select name="id_establecimiento" id="modalUsuarioEstablecimiento" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Selecciona un establecimiento</option>
                        <?php foreach ($establecimientos as $est): ?>
                            <option value="<?= $est['id'] ?>"><?= htmlspecialchars($est['etiqueta']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="errorEstablecimiento" class="text-red-600 text-sm mb-1 hidden pt-1">
                        <!-- El icono y mensaje se insertan por JS -->
                    </div>
                </div>
            </div>
            <div id="cambiarContrasenaWrapper" class="md:col-span-2 mt-4 mb-2 hidden">
                <label class="inline-flex items-center">
                    <input type="checkbox" id="checkCambiarContrasena" class="form-checkbox text-blue-600 align-middle" />
                    <span class="ml-4 text-sm text-gray-700 font-semibold">Habilitar Cambio de Contraseña</span>
                </label>
            </div>
            <div id="camposContrasena" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Contraseña</label>
                    <input type="password" name="contraseña" id="modalUsuarioContraseña" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="new-password" />
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Confirmar Contraseña</label>
                    <input type="password" name="confirmar_contraseña" id="modalUsuarioConfirmarContraseña" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="new-password" />
                    <div id="errorContrasena" class="text-red-600 text-sm mb-1 hidden pt-1">
                        <!-- El icono y mensaje se insertan por JS -->
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-6 gap-2">
                <button type="button" id="btnCancelarModal" class="hover:bg-red-400 bg-red-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-200">
                    Cancelar
                </button>
                <button type="submit" class="bg-[#0018F4] hover:bg-[#2746c7] text-white font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-200">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Generar Código de Acceso -->
<div id="modalAccessToken" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center border-b pb-3">
            <h2 class="text-xl font-semibold">Generar Código de Acceso</h2>
            <button id="btnCerrarModalAccessToken" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form id="formAccessToken" autocomplete="off">
            <div class="mt-4">
                <label class="block mb-1 font-semibold text-sm text-gray-700">Rol</label>
                <select name="rol" id="accessTokenRol" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Selecciona un rol</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Contador">Contador</option>
                    <option value="Vendedor">Vendedor</option>
                </select>
            </div>
            <div class="mt-4">
                <label class="block mb-1 font-semibold text-sm text-gray-700">Código generado</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly id="accessTokenCodigo" class="w-full border rounded px-3 py-2 bg-gray-100 text-lg font-mono" value="">
                    <button type="button" id="btnCopiarCodigo" class="text-gray-600 hover:text-blue-700" title="Copiar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2" fill="none" />
                            <rect x="3" y="3" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2" fill="none" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex justify-end mt-6 gap-2">
                <button type="button" id="btnCancelarModalAccessToken" class="hover:bg-red-400 bg-red-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-200">
                    Cancelar
                </button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-200">
                    Generar Código
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.ID_USUARIO_LOGUEADO = <?= json_encode($_SESSION['id_usuario']) ?>;
    window.ROL_USUARIO_LOGUEADO = <?= json_encode($_SESSION['rol']) ?>;
    window.ID_ESTABLECIMIENTO_LOGUEADO = <?= json_encode($_SESSION['id_establecimiento']) ?>;
    window.NOMBRE_ESTABLECIMIENTO_LOGUEADO = <?= json_encode($nombre_establecimiento_logueado) ?>;
</script>

<script src="../assets/js/usuario.js"></script>
<script src="../assets/js/access_token.js"></script>