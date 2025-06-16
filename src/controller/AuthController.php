<?php
require_once __DIR__ . '/../model/Usuario.php';

class AuthController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login()
    {
        if (isset($_SESSION['id_cliente'])) {
            header('Location: index.php?controller=home');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim($_POST['usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');

            $user = Usuario::autenticar($usuario, $contrasena);
            if ($user) {
                session_regenerate_id(true);
                $_SESSION['id_cliente'] = $user['id_cliente'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['id_usuario'] = $user['id'];
                $_SESSION['correo'] = $user['correo'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['ultima_actividad'] = time();
                header('Location: index.php?controller=home');
                exit;
            } else {
                $this->renderLoginView("Usuario o contraseña incorrectos");
            }
        } else {
            $this->renderLoginView();
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: index.php?controller=auth&action=login');
        exit;
    }

    public static function verificarSesionActiva()
    {
        if (!isset($_SESSION['id_cliente'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        define('TIEMPO_INACTIVIDAD', 600);
        if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad']) > TIEMPO_INACTIVIDAD) {
            session_unset();
            session_destroy();
            header('Location: index.php?controller=auth&action=login&error=Sesión expirada por inactividad');
            exit;
        }
        $_SESSION['ultima_actividad'] = time();
    }

    private function renderLoginView($error = '')
    {
        $contenido = 'view/components/login.php';
        require 'view/layout.php';
    }
}
