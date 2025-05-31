<?php
session_start();

if (
    !isset($_SESSION['id_cliente']) &&
    !(isset($_GET['controller']) && $_GET['controller'] === 'auth' && $_GET['action'] === 'login')
) {
    header('Location: index.php?controller=auth&action=login');
    exit;
}

$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';
$controlador = null;

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
    case 'home':
    default:
        $contenido = 'view/components/home.php';
        require 'view/layout.php';
        return;
}

if ($controlador && method_exists($controlador, $action)) {
    if (isset($_GET['id'])) {
        $controlador->$action($_GET['id']);
    } else {
        $controlador->$action();
    }
} else {
    header('HTTP/1.0 404 Not Found');
    echo '404 Not Found';
    exit;
}
