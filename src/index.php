<?php
require_once 'controller/AuthController.php';

session_start();

$controller = $_GET['controller'] ?? null;
$action = $_GET['action'] ?? null;
$controlador = null;

if (!isset($_SESSION['id_cliente'])) {
    if ($controller !== 'auth' || $action !== 'login') {
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
} else {
    if ($controller === 'auth' && $action === 'login') {
        header('Location: index.php?controller=home');
        exit;
    }
    if ($controller === null) {
        header('Location: index.php?controller=home');
        exit;
    }
}

$controller = $controller ?? (isset($_SESSION['id_cliente']) ? 'home' : 'auth');
$action = $action ?? (isset($_SESSION['id_cliente']) ? 'index' : 'login');

if ($controller !== 'auth') {
    AuthController::verificarSesionActiva();
}

switch ($controller) {
    case 'auth':
        require_once 'controller/AuthController.php';
        $controlador = new AuthController();
        break;
    case 'usuario':
        require_once 'controller/UsuarioController.php';
        $controlador = new UsuarioController();
        break;
    case 'cliente':
        require_once 'controller/ClienteController.php';
        $controlador = new ClienteController();
        break;
    case 'cuadres':
        require_once 'controller/CuadresController.php';
        $controlador = new CuadresController();
        break;
    case 'home':
        break;
    default:
        header('Location: index.php?controller=auth&action=login&error=Controlador no encontrado');
        exit;
}

if ($controlador) {
    if (method_exists($controlador, $action)) {
        if ($action === 'cambiarEstado') {
            $id = $_GET['id'] ?? null;
            $estado = $_GET['estado'] ?? null;
            $controlador->$action($id, $estado);
        } else {
            $controlador->$action();
        }
    } else {
        header('Location: index.php?controller=auth&action=login&error=Acción no encontrada');
        exit;
    }
}
