<?php
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';
$controlador = null;

switch ($controller) {
    case 'usuario':
        require_once 'controller/UsuarioController.php';
        $controlador = new UsuarioController();
        break;
    case 'cliente':
        require_once 'controller/ClienteController.php';
        $controlador = new ClienteController();
        break;
    case 'home':
        // Dashboard-Home principal
    default:
        $contenido = 'view/components/home.php';
        require 'view/layout.php';
        return; // Evita que intente ejecutar una acción no válida
}

// Validación de existencia de controlador y método
if ($controlador && method_exists($controlador, $action)) {
    $id = $_GET['id'] ?? null;
    $controlador->$action($id);
} else {
    die("Controlador o acción no válidos");
}