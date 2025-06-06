<?php
require_once 'model/Usuario.php';

class UsuarioController
{
    public function index()
    {
        $id_cliente = $_SESSION['id_cliente'];
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        $sort = $_GET['sort'] ?? 'sucursal';
        $dir = ($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $usuarios = Usuario::obtenerPaginado($id_cliente, $limit, $offset, $sort, $dir);
        $total = Usuario::contarPorCliente($id_cliente);
        $sucursales = Usuario::obtenerSucursalesPorCliente($id_cliente);
        $correo_cliente = Usuario::obtenerCorreoCliente($id_cliente);
        $contenido = 'view/components/usuario.php';
        require 'view/layout.php';
    }

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim(strip_tags($_POST['usuario']));
            $rol = trim(strip_tags($_POST['rol']));
            $id_sucursal = intval($_POST['id_sucursal']);
            $estado = 1;
            $id_cliente = $_SESSION['id_cliente'];
            $contraseña = $_POST['contraseña'];
            $confirmar_contraseña = $_POST['confirmar_contraseña'];

            if ($contraseña !== $confirmar_contraseña) {
                header('Location: index.php?controller=usuario&error=Las contraseñas no coinciden');
                exit;
            }

            $hashed_password = password_hash($contraseña, PASSWORD_BCRYPT);
            Usuario::insertar($usuario, $rol, $id_sucursal, $estado, $id_cliente, $hashed_password);
            header('Location: index.php?controller=usuario');
            exit;
        }
        header('Location: index.php?controller=usuario');
        exit;
    }

    public function editar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = intval($_POST['id_usuario']);
            $usuario = trim(strip_tags($_POST['usuario']));
            $rol = trim(strip_tags($_POST['rol']));
            $id_sucursal = intval($_POST['id_sucursal']);
            $estado = intval($_POST['estado']);
            $id_cliente = $_SESSION['id_cliente'];
            $contraseña = $_POST['contraseña'];
            $confirmar_contraseña = $_POST['confirmar_contraseña'];
            if ($id_usuario <= 0 || empty($usuario) || empty($rol) || $id_sucursal <= 0 || !in_array($estado, [1, 2, 3])) {
                header('Location: index.php?controller=usuario&error=Datos inválidos');
                exit;
            }

            if (!empty($contraseña) || !empty($confirmar_contraseña)) {
                if ($contraseña !== $confirmar_contraseña) {
                    header('Location: index.php?controller=usuario&error=Las contraseñas no coinciden');
                    exit;
                }
                $hashed_password = password_hash($contraseña, PASSWORD_BCRYPT);
            } else {
                $hashed_password = null;
            }

            $resultado = Usuario::actualizar($id_usuario, $usuario, $rol, $id_sucursal, $estado, $id_cliente, $hashed_password);

            if ($resultado) {
                header('Location: index.php?controller=usuario');
            } else {
                header('Location: index.php?controller=usuario&error=No se pudo actualizar el usuario');
            }
            exit;
        }
        header('Location: index.php?controller=usuario');
        exit;
    }

    public function cambiarEstado($id)
    {
        $id = intval($id);
        $estado = isset($_GET['estado']) ? intval($_GET['estado']) : null;
        if ($id <= 0 || !in_array($estado, [1, 2, 3])) {
            header('Location: index.php?controller=usuario&error=Datos inválidos');
            exit;
        }
        Usuario::cambiarEstado($id, $estado);
        header('Location: index.php?controller=usuario');
        exit;
    }
}
