<?php
require_once 'model/Establecimiento.php';

class EstablecimientoController
{
    public function index()
    {
        $this->verificarSesion();

        $id_cliente = $_SESSION['id_cliente'];
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;

        $establecimientos = Establecimiento::obtenerPorCliente($id_cliente, $limit, $offset);
        $total = Establecimiento::contarPorCliente($id_cliente);

        $contenido = 'view/components/establecimiento.php';
        require 'view/layout.php';
    }

    public function crear()
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->limpiarDatos($_POST);
            $datos['user_create'] = $_SESSION['usuario'];
            $datos['user_update'] = $_SESSION['usuario'];
            $error = $this->validarDatos($datos);

            if (!$error && Establecimiento::existeRuc($datos['ruc'])) {
                $error = 'El RUC ya existe para otro establecimiento.';
            }

            if ($error) {
                $mensaje = $error;
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            Establecimiento::insertar($datos);
            header('Location: index.php?controller=establecimiento');
            exit;
        }
    }

    public function editar()
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->limpiarDatos($_POST);
            $id = intval($datos['id']);
            $datos['user_update'] = $_SESSION['usuario'];
            $error = $this->validarDatos($datos, true);

            if (!$error && Establecimiento::existeRuc($datos['ruc'], $id)) {
                $error = 'El RUC ya existe para otro establecimiento.';
            }

            if ($error) {
                $mensaje = $error;
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            Establecimiento::actualizar($id, $datos);
            header('Location: index.php?controller=establecimiento');
            exit;
        }
    }

    public function cambiarEstado($id, $estado)
    {
        $this->verificarSesion();

        $id = intval($id);
        $estado = intval($estado);
        $id_cliente = $_SESSION['id_cliente'];

        if ($id <= 0 || !in_array($estado, [1, 2, 3])) {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
            return;
        }

        $resultado = Establecimiento::cambiarEstado($id, $estado, $id_cliente);

        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo cambiar el estado']);
        }
    }

    public function verificarRuc()
    {
        $this->verificarSesion();

        $ruc = trim($_GET['ruc'] ?? '');
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $existe = Establecimiento::existeRuc($ruc, $id);
        header('Content-Type: application/json');
        echo json_encode(['existe' => $existe]);
        exit;
    }

    private function limpiarDatos($data)
    {
        return [
            'id' => isset($data['id']) ? intval($data['id']) : 0,
            'ruc' => trim(strip_tags($data['ruc'] ?? '')),
            'razon_social' => trim(strip_tags($data['razon_social'] ?? '')),
            'direccion' => trim(strip_tags($data['direccion'] ?? '')),
            'id_cliente' => $_SESSION['id_cliente'],
            'estado' => isset($data['estado']) ? intval($data['estado']) : 1
        ];
    }

    private function validarDatos($datos, $esEdicion = false)
    {
        if ($esEdicion && $datos['id'] <= 0) {
            return 'ID de establecimiento inválido';
        }
        if (empty($datos['ruc']) || empty($datos['razon_social']) || empty($datos['direccion'])) {
            return 'Todos los campos son obligatorios';
        }
        if (!preg_match('/^\d{11}$/', $datos['ruc'])) {
            return 'El RUC debe tener 11 dígitos numéricos';
        }
        return false;
    }

    private function verificarSesion()
    {
        if (!isset($_SESSION['id_cliente'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }
}
