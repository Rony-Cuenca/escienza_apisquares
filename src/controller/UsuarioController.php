<?php
require_once 'model/Usuario.php';

class UsuarioController
{
    public function index()
    {
        $id_cliente = $_SESSION['id_cliente'];
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $sort = $_GET['sort'] ?? 'sucursal';
        $dir = ($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $usuarios = Usuario::obtenerPaginado($id_cliente, $limit, $offset, $sort, $dir);
        $total = Usuario::contarPorCliente($id_cliente);

        $contenido = 'view/components/usuario.php';
        require 'view/layout.php';
    }

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_POST['usuario'];
            $rol = $_POST['rol'];
            $id_sucursal = $_POST['id_sucursal'];
            $estado = $_POST['estado'];
            $id_cliente = $_SESSION['id_cliente'];

            Usuario::insertar($usuario, $rol, $id_sucursal, $estado, $id_cliente);
            header('Location: index.php?controller=usuario');
            exit;
        }
        header('Location: index.php?controller=usuario');
        exit;
    }

    public function editar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = $_POST['id_usuario'];
            $usuario = $_POST['usuario'];
            $rol = $_POST['rol'];
            $id_sucursal = $_POST['id_sucursal'];
            $estado = $_POST['estado'];
            $id_cliente = $_SESSION['id_cliente'];

            Usuario::actualizar($id_usuario, $usuario, $rol, $id_sucursal, $estado, $id_cliente);
            header('Location: index.php?controller=usuario');
            exit;
        }
        header('Location: index.php?controller=usuario');
        exit;
    }

    public function cambiarEstado($id)
    {
        $estado = $_GET['estado'] ?? null;
        if ($estado !== null) {
            Usuario::cambiarEstado($id, $estado);
        }
        header('Location: index.php?controller=usuario');
        exit;
    }
}
