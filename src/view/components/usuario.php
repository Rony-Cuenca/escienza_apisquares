<div class="flex flex-col items-center min-h-[calc(100vh-120px)] w-full px-[50px] pt-[30px]">
    <div class="w-full max-w-7xl">
        <div class="mb-4 flex justify-end">
            <button id="btnNuevoUsuario"
                class="bg-[#0018F4] hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow"
                data-correo-cliente="<?= htmlspecialchars($correo_cliente) ?>">
                Nuevo
            </button>
        </div>
        <div class="overflow-x-auto md:overflow-x-visible">
            <table class="min-w-full border-collapse border-[2px] border-[#0018F4] shadow bg-white mx-auto">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-2 text-center font-semibold border-b border-[#0018F4] w-[50px]"><strong>#</strong></th>
                        <th class="py-2 px-2 text-left font-semibold border-b border-[#0018F4] w-[120px]">
                            <a href="index.php?controller=usuario&sort=usuario&dir=<?= $sort === 'usuario' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>USUARIO</strong></a>
                        </th>
                        <th class="py-2 px-2 text-left font-semibold border-b border-[#0018F4] w-[120px]">
                            <a href="index.php?controller=usuario&sort=estado&dir=<?= $sort === 'estado' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ESTADO</strong></a>
                        </th>
                        <th class="py-2 px-2 text-left font-semibold border-b border-[#0018F4] w-[120px]">
                            <a href="index.php?controller=usuario&sort=rol&dir=<?= $sort === 'rol' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ROL</strong></a>
                        </th>
                        <th class="py-2 px-2 text-left font-semibold border-b border-[#0018F4] w-[220px]">
                            <a href="index.php?controller=usuario&sort=sucursal&dir=<?= $sort === 'sucursal' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>SUCURSAL</strong></a>
                        </th>
                        <th class="py-2 px-2 border-b border-[#0018F4] w-[40px]"><strong></strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($usuarios): ?>
                        <?php $i = 1;
                        foreach ($usuarios as $row): ?>
                            <tr class="border-b border-[#0018F4] hover:bg-blue-50 transition">
                                <td class="py-2 px-2 text-center"><?= $i++ ?></td>
                                <td class="py-2 px-2"><?= htmlspecialchars($row['usuario']) ?></td>
                                <td class="py-2 px-2">
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
                                <td class="py-2 px-2"><?= htmlspecialchars($row['rol']) ?></td>
                                <td class="py-2 px-2"><?= htmlspecialchars($row['sucursal']) ?></td>
                                <td class="py-2 px-2 text-center relative">
                                    <button data-action="toggleMenu" type="button" class="focus:outline-none" aria-label="Abrir menú de usuario">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24">
                                            <circle cx="12" cy="5" r="2" fill="#000" />
                                            <circle cx="12" cy="12" r="2" fill="#000" />
                                            <circle cx="12" cy="19" r="2" fill="#000" />
                                        </svg>
                                    </button>
                                    <div class="hidden absolute right-0 mt-2 w-32 bg-white rounded-lg shadow-lg z-10 border min-w-[120px]">
                                        <a href="javascript:void(0);"
                                            data-action="editar"
                                            data-id="<?= $row['id'] ?>"
                                            data-usuario="<?= htmlspecialchars($row['usuario']) ?>"
                                            data-correo="<?= htmlspecialchars($correo_cliente) ?>"
                                            data-rol="<?= htmlspecialchars($row['rol']) ?>"
                                            data-sucursal="<?= $row['id_sucursal'] ?>"
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
                            <td colspan="6" class="py-4 px-2 text-center text-gray-500">No hay usuarios para este cliente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="flex justify-between items-center mt-4 px-[20px] py-1">
                <div class="text-sm text-gray-600">
                    Mostrando <?= $offset + 1 ?> a <?= min($offset + $limit, $total) ?> de <?= $total ?> usuarios
                </div>
                <div class="flex gap-2">
                    <?php for ($i = 1; $i <= ceil($total / $limit); $i++): ?>
                        <a href="index.php?controller=usuario&page=<?= $i ?>&limit=<?= $limit ?>"
                            class="px-3 py-1 border rounded <?= $i == $page ? 'bg-[#0018F4] text-white' : 'bg-gray-100 text-gray-700' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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
                    <input type="text" name="usuario" id="modalUsuarioNombre" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Correo electrónico</label>
                    <input type="email" name="correo" id="modalUsuarioCorreo" readonly
                        class="w-full border rounded px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Rol</label>
                    <select name="rol" id="modalUsuarioRol" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Selecciona un rol</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Contador">Contador</option>
                        <option value="Vendedor">Vendedor</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Sucursal</label>
                    <select name="id_sucursal" id="modalUsuarioSucursal" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Selecciona una sucursal</option>
                        <?php foreach ($sucursales as $suc): ?>
                            <option value="<?= $suc['id'] ?>"><?= htmlspecialchars($suc['razon_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Contraseña</label>
                    <input type="password" name="contraseña" id="modalUsuarioContraseña" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Confirmar Contraseña</label>
                    <input type="password" name="confirmar_contraseña" id="modalUsuarioConfirmarContraseña" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
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

<div id="modalConfirmacion" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 text-center relative">
        <h2 class="text-xl font-semibold mb-4">Confirmación</h2>
        <div id="modalAnimacion" class="flex justify-center mb-4 hidden"></div>
        <p id="modalMensaje" class="text-gray-700 mb-6">¿Estás seguro de cambiar el estado de este usuario?</p>
        <div id="modalBotones" class="flex justify-end gap-4">
            <button id="btnCancelarConfirmacion" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg">
                Cancelar
            </button>
            <button id="btnAceptarConfirmacion" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                Aceptar
            </button>
        </div>
    </div>
</div>

<script src="../assets/js/usuario.js"></script>