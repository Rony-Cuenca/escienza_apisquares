<?php
require_once __DIR__ . '/../../config/conexion.php';
$conn = Conexion::conectar();

// Obtener el id_cliente del usuario logueado
$id_cliente = $_SESSION['id_cliente'];

$sql = "
SELECT 
    u.id,
    u.usuario,
    u.estado,
    u.rol,
    s.razon_social AS sucursal,
    u.id_sucursal
FROM usuario u
INNER JOIN sucursal s ON u.id_sucursal = s.id
WHERE u.id_cliente = $id_cliente
ORDER BY s.razon_social ASC, u.rol ASC, u.id ASC
";
$result = $conn->query($sql);

$sqlSuc = "SELECT id, razon_social FROM sucursal WHERE id_cliente = $id_cliente";
$sucursales = $conn->query($sqlSuc);

// Para recargar sucursales en el formulario al editar
$sucursalesArr = [];
$conn2 = Conexion::conectar();
$resSuc = $conn2->query("SELECT id, razon_social FROM sucursal WHERE id_cliente = $id_cliente");
while ($suc = $resSuc->fetch_assoc()) {
    $sucursalesArr[] = $suc;
}
?>

<div class="flex flex-col items-center min-h-[calc(100vh-120px)] w-full px-2 pt-6 pb-2">
    <div class="w-full max-w-7xl">
        <div class="mb-4 flex justify-end">
            <button id="btnNuevoUsuario"
                class="bg-[#0018F4] hover:bg-[#2746c7] text-white font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-200">
                Nuevo
            </button>
        </div>
        <form id="formNuevoUsuario" class="mb-6 bg-white rounded-xl shadow p-6 w-full max-w-2xl mx-auto hidden border border-[#0018F4]" method="post" action="index.php?controller=usuario&action=crear" autocomplete="off">
            <input type="hidden" name="id_usuario" id="id_usuario">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Nombre de usuario</label>
                    <input type="text" name="usuario" id="usuario" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Rol</label>
                    <select name="rol" id="rol" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Selecciona un rol</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Contador">Contador</option>
                        <option value="Vendedor">Vendedor</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Sucursal</label>
                    <select name="id_sucursal" id="id_sucursal" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Selecciona una sucursal</option>
                        <?php foreach ($sucursalesArr as $suc): ?>
                            <option value="<?= $suc['id'] ?>"><?= htmlspecialchars($suc['razon_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Estado</label>
                    <select name="estado" id="estado" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="1">Activo</option>
                        <option value="2">Inactivo</option>
                        <option value="3">Deshabilitado</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="id_cliente" value="<?= $id_cliente ?>">
            <div class="flex justify-end mt-6 gap-2">
                <button type="button" id="btnCancelar" class="hover:bg-red-400 bg-red-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-200">
                    Cancelar
                </button>
                <button type="submit" class="bg-[#0018F4] hover:bg-[#2746c7] text-white font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-200">
                    Guardar
                </button>
            </div>
        </form>
        <div class="overflow-x-auto md:overflow-x-visible">
            <table class="min-w-full border-separate border-spacing-0 border-[2px] border-[#0018F4] rounded-xl shadow bg-white mx-auto">
                <thead>
                    <thead>
                        <tr class="bg-[#A9C3E8]">
                            <th class="py-2 px-2 text-center font-semibold border-b border-blue-600 w-[50px]"><strong>#</strong></th>
                            <th class="py-2 px-2 text-left font-semibold border-b border-blue-600 w-[120px]">
                                <a href="index.php?controller=usuario&sort=usuario&dir=<?= $sort === 'usuario' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>USUARIO</strong></a>
                            </th>
                            <th class="py-2 px-2 text-left font-semibold border-b border-blue-600 w-[120px]">
                                <a href="index.php?controller=usuario&sort=estado&dir=<?= $sort === 'estado' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ESTADO</strong></a>
                            </th>
                            <th class="py-2 px-2 text-left font-semibold border-b border-blue-600 w-[120px]">
                                <a href="index.php?controller=usuario&sort=rol&dir=<?= $sort === 'rol' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>ROL</strong></a>
                            </th>
                            <th class="py-2 px-2 text-left font-semibold border-b border-blue-600 w-[220px]">
                                <a href="index.php?controller=usuario&sort=sucursal&dir=<?= $sort === 'sucursal' && $dir === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:underline"><strong>SUCURSAL</strong></a>
                            </th>
                            <th class="py-2 px-2 border-b border-blue-600 w-[40px]"><strong></strong></th>
                        </tr>
                    </thead>
                <tbody>
                    <?php if ($usuarios): ?>
                        <?php $i = 1;
                        foreach ($usuarios as $row): ?>
                            <tr class="border-b border-blue-600 hover:bg-blue-50 transition">
                                <td class="py-2 px-2 text-center"><?= $i++ ?></td>
                                <td class="py-2 px-2"><?= htmlspecialchars($row['usuario']) ?></td>
                                <td class="py-2 px-2">
                                    <?php if ($row['estado'] == 1): ?>
                                        <span
                                            class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-green-200 text-green-800 text-sm font-medium cursor-pointer transition hover:bg-green-300"
                                            onclick="cambiarEstadoUsuario(<?= $row['id'] ?>, 2)"
                                            title="Cambiar a inactivo">
                                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Activo
                                        </span>
                                    <?php elseif ($row['estado'] == 2): ?>
                                        <span
                                            class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-red-200 text-red-800 text-sm font-medium cursor-pointer transition hover:bg-red-300"
                                            onclick="cambiarEstadoUsuario(<?= $row['id'] ?>, 1)"
                                            title="Cambiar a activo">
                                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>Inactivo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm font-medium cursor-not-allowed" style="opacity:0.9;">
                                            Deshabilitado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-2"><?= htmlspecialchars($row['rol']) ?></td>
                                <td class="py-2 px-2"><?= htmlspecialchars($row['sucursal']) ?></td>
                                <td class="py-2 px-2 text-center relative">
                                    <button onclick="toggleMenu(this)" type="button" class="focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24">
                                            <circle cx="12" cy="5" r="2" fill="#000" />
                                            <circle cx="12" cy="12" r="2" fill="#000" />
                                            <circle cx="12" cy="19" r="2" fill="#000" />
                                        </svg>
                                    </button>
                                    <div class="hidden absolute right-0 mt-2 w-32 bg-white rounded-lg shadow-lg z-10 border" style="min-width:120px;">
                                        <a href="#"
                                            onclick="editarUsuario(this)"
                                            data-id="<?= $row['id'] ?>"
                                            data-usuario="<?= htmlspecialchars($row['usuario']) ?>"
                                            data-rol="<?= htmlspecialchars($row['rol']) ?>"
                                            data-sucursal="<?= $row['id_sucursal'] ?>"
                                            data-estado="<?= $row['estado'] ?>"
                                            class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-black">Editar</a>
                                        <?php if ($row['estado'] != 3): ?>
                                            <a href="index.php?controller=usuario&action=cambiarEstado&id=<?= $row['id'] ?>&estado=3"
                                                class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">Deshabilitar</a>
                                        <?php else: ?>
                                            <a href="javascript:void(0);" onclick="habilitarUsuario(<?= $row['id'] ?>)"
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
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right bg-[#b2cbe8] rounded-b-xl px-4 py-3">
                            <span class="mr-4 font-semibold">Filas por página: <?= $limit ?></span>
                            <?php if ($page > 1): ?>
                                <a href="index.php?controller=usuario&page=<?= $page - 1 ?>&sort=<?= $sort ?>&dir=<?= $dir ?>" class="mx-2 hover:underline">&lt;</a>
                            <?php endif; ?>
                            <span class="mx-2"><?= (($page - 1) * $limit + 1) ?>-<?= min($page * $limit, $total) ?> de <?= $total ?></span>
                            <?php if ($page < ceil($total / $limit)): ?>
                                <a href="index.php?controller=usuario&page=<?= $page + 1 ?>&sort=<?= $sort ?>&dir=<?= $dir ?>" class="mx-2 hover:underline">&gt;</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    // Mostrar formulario para nuevo usuario
    document.getElementById('btnNuevoUsuario').addEventListener('click', function() {
        const form = document.getElementById('formNuevoUsuario');
        if (!form.classList.contains('hidden')) {
            form.classList.add('hidden');
            form.reset();
            form.action = 'index.php?controller=usuario&action=crear';
            document.getElementById('id_usuario').value = '';
            document.getElementById('estado').value = 1;
        } else {
            form.reset();
            form.action = 'index.php?controller=usuario&action=crear';
            document.getElementById('id_usuario').value = '';
            document.getElementById('estado').value = 1;
            form.classList.remove('hidden');
        }
    });

    // Mostrar formulario para editar usuario y rellenar campos
    function editarUsuario(btn) {
        document.querySelectorAll('td .absolute').forEach(menu => menu.classList.add('hidden'));
        const form = document.getElementById('formNuevoUsuario');
        form.classList.remove('hidden');
        form.action = 'index.php?controller=usuario&action=editar&id=' + btn.dataset.id;
        document.getElementById('id_usuario').value = btn.dataset.id;
        document.getElementById('usuario').value = btn.dataset.usuario;
        document.getElementById('rol').value = btn.dataset.rol;
        document.getElementById('id_sucursal').value = btn.dataset.sucursal;
        // Nuevo: asignar el estado si lo pasas como data-estado
        if (btn.dataset.estado) {
            document.getElementById('estado').value = btn.dataset.estado;
        }
        document.getElementById('btnCancelar').classList.remove('hidden');
    }

    document.getElementById('btnCancelar').addEventListener('click', function() {
        const form = document.getElementById('formNuevoUsuario');
        form.classList.add('hidden');
        form.reset();
        form.action = 'index.php?controller=usuario&action=crear';
        document.getElementById('id_usuario').value = '';
        document.getElementById('estado').value = 1;
        this.classList.add('hidden');
    });

    // Habilitar usuario deshabilitado
    function habilitarUsuario(id) {
        window.location.href = 'index.php?controller=usuario&action=cambiarEstado&id=' + id + '&estado=1';
    }

    // Menú de 3 puntos
    function toggleMenu(btn) {
        document.querySelectorAll('td .absolute').forEach(menu => {
            if (!menu.contains(btn)) menu.classList.add('hidden');
        });
        const menu = btn.nextElementSibling;
        menu.classList.toggle('hidden');
        document.addEventListener('click', function handler(e) {
            if (!btn.parentNode.contains(e.target)) {
                menu.classList.add('hidden');
                document.removeEventListener('click', handler);
            }
        });
    }

    function cambiarEstadoUsuario(id, nuevoEstado) {
        fetch(`index.php?controller=usuario&action=cambiarEstado&id=${id}&estado=${nuevoEstado}`)
            .then(() => location.reload());
    }
</script>