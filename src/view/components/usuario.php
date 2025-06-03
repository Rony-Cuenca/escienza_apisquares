<?php
require_once __DIR__ . '/../../config/conexion.php';
$conn = Conexion::conectar();

$id_cliente = $_SESSION['id_cliente'];

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$offset = ($page - 1) * $limit;

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'sucursal';
$dir = (isset($_GET['dir']) && strtoupper($_GET['dir']) === 'DESC') ? 'DESC' : 'ASC';
$allowedSort = ['usuario', 'estado', 'rol', 'sucursal'];
if (!in_array($sort, $allowedSort)) $sort = 'sucursal';

$stmtTotal = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE id_cliente = ?");
$stmtTotal->bind_param("i", $id_cliente);
$stmtTotal->execute();
$total = $stmtTotal->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("
    SELECT 
        u.id,
        u.usuario,
        u.estado,
        u.rol,
        s.razon_social AS sucursal,
        u.id_sucursal
    FROM usuario u
    INNER JOIN sucursal s ON u.id_sucursal = s.id
    WHERE u.id_cliente = ?
    ORDER BY $sort $dir, u.id ASC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $id_cliente, $limit, $offset);
$stmt->execute();
$usuarios = $stmt->get_result();

$sucursales = [];
$stmtSuc = $conn->prepare("SELECT id, razon_social FROM sucursal WHERE id_cliente = ?");
$stmtSuc->bind_param("i", $id_cliente);
$stmtSuc->execute();
$resSuc = $stmtSuc->get_result();
while ($suc = $resSuc->fetch_assoc()) {
    $sucursales[] = $suc;
}
?>

<div class="flex flex-col items-center min-h-[calc(100vh-120px)] w-full px-[50px] pt-[30px]">
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
                        <?php foreach ($sucursales as $suc): ?>
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
                <button type="button" id="btnCancelar" class="hover:bg-red-400 bg-red-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-200 hidden">
                    Cancelar
                </button>
                <button type="submit" class="bg-[#0018F4] hover:bg-[#2746c7] text-white font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-200">
                    Guardar
                </button>
            </div>
        </form>
        <div class="overflow-x-auto md:overflow-x-visible">
            <table class="min-w-full border-collapse border-[2px] border-[#0018F4] shadow bg-white mx-auto">
                <thead>
                    <tr class="bg-[#A9C3E8]">
                        <th class="py-2 px-2 text-center font-semibold border-b border-[#0018F4] w-[50px] rounded-tl-xl"><strong>#</strong></th>
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
                        <th class="py-2 px-2 border-b border-[#0018F4] w-[40px] rounded-tr-xl"><strong></strong></th>
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
                                            onclick="cambiarEstado(<?= $row['id'] ?>, 2)"
                                            title="Cambiar a inactivo"
                                            tabindex="0"
                                            role="button"
                                            aria-pressed="true">
                                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>Activo
                                        </span>
                                    <?php elseif ($row['estado'] == 2): ?>
                                        <span
                                            class="inline-flex items-center justify-center w-28 px-3 py-1 rounded-full bg-red-200 text-yellow-800 text-sm font-medium cursor-pointer transition hover:bg-red-300"
                                            onclick="cambiarEstado(<?= $row['id'] ?>, 1)"
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
                                    <button onclick="toggleMenu(this)" type="button" class="focus:outline-none" aria-label="Abrir menú de usuario">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24">
                                            <circle cx="12" cy="5" r="2" fill="#000" />
                                            <circle cx="12" cy="12" r="2" fill="#000" />
                                            <circle cx="12" cy="19" r="2" fill="#000" />
                                        </svg>
                                    </button>
                                    <div class="hidden absolute right-0 mt-2 w-32 bg-white rounded-lg shadow-lg z-10 border min-w-[120px]">
                                        <a href="javascript:void(0);"
                                            onclick="editarUsuario(this)"
                                            data-id="<?= $row['id'] ?>"
                                            data-usuario="<?= htmlspecialchars($row['usuario']) ?>"
                                            data-rol="<?= htmlspecialchars($row['rol']) ?>"
                                            data-sucursal="<?= $row['id_sucursal'] ?>"
                                            data-estado="<?= $row['estado'] ?>"
                                            class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-black">Editar</a>
                                        <?php if ($row['estado'] != 3): ?>
                                            <a href="javascript:void(0);" onclick="cambiarEstado(<?= $row['id'] ?>, 3)"
                                                class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">Deshabilitar</a>
                                        <?php else: ?>
                                            <a href="javascript:void(0);" onclick="cambiarEstado(<?= $row['id'] ?>, 1)"
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
                        <td colspan="6" class="text-right bg-[#A9C3E8] rounded-bl-xl rounded-br-xl px-4 py-3">
                            <form id="formFilasPorPagina" method="get" style="display:inline;">
                                <input type="hidden" name="controller" value="usuario">
                                <input type="hidden" name="page" value="<?= $page ?>">
                                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                                <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                                <label for="limit" class="mr-2 font-semibold">Filas por página:</label>
                                <select name="limit" id="limit" class="border rounded px-1 py-0.5" onchange="this.form.submit()">
                                    <?php foreach ([5, 10, 20, 50] as $op): ?>
                                        <option value="<?= $op ?>" <?= $limit == $op ? 'selected' : '' ?>><?= $op ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($page > 1): ?>
                                <a href="index.php?controller=usuario&page=<?= $page - 1 ?>&limit=<?= $limit ?>&sort=<?= $sort ?>&dir=<?= $dir ?>" class="mx-2 hover:underline">&lt;</a>
                            <?php else: ?>
                                <span class="mx-2 text-gray-400 cursor-not-allowed">&lt;</span>
                            <?php endif; ?>
                            <span class="mx-2"><?= (($page - 1) * $limit + 1) ?>-<?= min($page * $limit, $total) ?> de <?= $total ?></span>
                            <?php if ($page < ceil($total / $limit)): ?>
                                <a href="index.php?controller=usuario&page=<?= $page + 1 ?>&limit=<?= $limit ?>&sort=<?= $sort ?>&dir=<?= $dir ?>" class="mx-2 hover:underline">&gt;</a>
                            <?php else: ?>
                                <span class="mx-2 text-gray-400 cursor-not-allowed">&gt;</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    function mostrarForm({
        id = '',
        usuario = '',
        rol = '',
        sucursal = '',
        estado = 1,
        esEdicion = false
    }) {
        const form = document.getElementById('formNuevoUsuario');
        form.classList.remove('hidden');
        form.action = esEdicion ?
            'index.php?controller=usuario&action=editar&id=' + id :
            'index.php?controller=usuario&action=crear';
        document.getElementById('id_usuario').value = id;
        document.getElementById('usuario').value = usuario;
        document.getElementById('rol').value = rol;
        document.getElementById('id_sucursal').value = sucursal;
        document.getElementById('estado').value = estado;
        document.getElementById('btnCancelar').classList.remove('hidden');
    }

    function ocultarForm() {
        const form = document.getElementById('formNuevoUsuario');
        form.classList.add('hidden');
        form.reset();
        form.action = 'index.php?controller=usuario&action=crear';
        document.getElementById('id_usuario').value = '';
        document.getElementById('estado').value = 1;
        document.getElementById('btnCancelar').classList.add('hidden');
    }

    document.getElementById('btnNuevoUsuario').addEventListener('click', function() {
        mostrarForm({});
    });

    function editarUsuario(btn) {
        document.querySelectorAll('td .absolute').forEach(menu => menu.classList.add('hidden'));
        mostrarForm({
            id: btn.dataset.id,
            usuario: btn.dataset.usuario,
            rol: btn.dataset.rol,
            sucursal: btn.dataset.sucursal,
            estado: btn.dataset.estado,
            esEdicion: true
        });
    }

    document.getElementById('btnCancelar').addEventListener('click', ocultarForm);

    // Menú de 3 puntos
    function toggleMenu(btn) {
        document.querySelectorAll('td .absolute').forEach(menu => {
            if (!menu.contains(btn)) menu.classList.add('hidden');
        });
        const menu = btn.nextElementSibling;
        menu.classList.toggle('hidden');

        function handler(e) {
            if (!btn.parentNode.contains(e.target)) {
                menu.classList.add('hidden');
                document.removeEventListener('click', handler);
            }
        }
        setTimeout(() => document.addEventListener('click', handler), 0);
    }

    function cambiarEstado(id, nuevoEstado) {
        fetch(`index.php?controller=usuario&action=cambiarEstado&id=${id}&estado=${nuevoEstado}`)
            .then(() => location.reload());
    }
</script>