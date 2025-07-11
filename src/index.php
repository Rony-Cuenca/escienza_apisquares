<?php
require_once 'controller/AuthController.php';

session_start();

$controller = $_GET['controller'] ?? null;
$action = $_GET['action'] ?? null;
$controlador = null;

if (!isset($_SESSION['id_cliente']) || $_SESSION['id_cliente'] === 0) {
    $accionesPublicas = [
        'auth' => ['login', 'register'],
        'accessToken' => ['validar'],
        'usuario' => ['verificarUsuario', 'verificarCorreo'],
        'superadmin' => ['index', 'clientes', 'verCliente', 'accesoDirectoEstablecimiento', 'salirAccesoDirecto'],
        'cliente' => ['index', 'store', 'show', 'consultarRuc', 'editar', 'obtenerDatos', 'cambiarEstado']
    ];

    if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true) {
        // SuperAdmin tiene acceso completo
    } elseif (!isset($accionesPublicas[$controller]) || !in_array($action, $accionesPublicas[$controller])) {
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
} else {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'SuperAdmin') {
        $_SESSION['is_super_admin'] = true;
    }

    if ($controller === 'auth' && $action === 'login') {
        header('Location: index.php?controller=home');
        exit;
    }
    if ($controller === null) {
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'SuperAdmin') {
            header('Location: index.php?controller=superadmin');
            exit;
        }
        header('Location: index.php?controller=home');
        exit;
    }
    if ($controller === 'superadmin') {
        $esSuperAdmin = (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true) ||
            (isset($_SESSION['rol']) && $_SESSION['rol'] === 'SuperAdmin');

        if (!$esSuperAdmin) {
            header('Location: index.php?controller=home');
            exit;
        }
    }
}

$controller = $controller ?? (isset($_SESSION['id_cliente']) ? 'home' : 'auth');
$action = $action ?? (isset($_SESSION['id_cliente']) ? 'index' : 'login');

if (
    isset($_SESSION['id_cliente']) &&
    !($controller === 'auth' && in_array($action, ['login', 'register'])) &&
    !($controller === 'accessToken' && $action === 'validar')
) {
    AuthController::verificarSesionActiva();
}


switch ($controller) {
    case 'auth':
        require_once 'controller/AuthController.php';
        $controlador = new AuthController();
        break;
    case 'superadmin':
        require_once 'controller/SuperAdminController.php';
        $controlador = new SuperAdminController();
        break;
    case 'cliente':
        require_once 'controller/ClienteController.php';
        $controlador = new ClienteController();
        break;
    case 'usuario':
        require_once 'controller/UsuarioController.php';
        $controlador = new UsuarioController();
        break;
    case 'establecimiento':
        require_once 'controller/EstablecimientoController.php';
        $controlador = new EstablecimientoController();
        break;
    case 'cuadres':
        require_once 'controller/CuadresController.php';
        $controlador = new CuadresController();
        break;
    case 'home':
        require_once 'controller/HomeController.php';
        $controlador = new HomeController();
        break;
    case 'reporte':
        require_once 'controller/ReporteController.php';
        $controlador = new ReporteController();
        break;
    case 'accessToken':
        require_once 'controller/AccessTokenController.php';
        $controlador = new AccessTokenController();
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
        } elseif ($action === 'show' && $controller === 'cliente') {
            $id = $_GET['id'] ?? null;
            $controlador->$action($id);
        } else {
            $controlador->$action();
        }
    } else {
        header('Location: index.php?controller=auth&action=login&error=Acción no encontrada');
        exit;
    }
}
