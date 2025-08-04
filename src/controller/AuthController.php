<?php
require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../model/AccessToken.php';

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
                
                if ($user['rol'] == 'SuperAdmin') {
                    $_SESSION['id_cliente'] = $user['id_cliente'];
                    $_SESSION['usuario'] = $user['usuario'];
                    $_SESSION['id_usuario'] = $user['id'];
                    $_SESSION['correo'] = $user['correo'];
                    $_SESSION['rol'] = $user['rol'];
                    $_SESSION['id_establecimiento'] = $user['id_establecimiento'];
                    $_SESSION['is_super_admin'] = true;
                    $_SESSION['ultima_actividad'] = time();
                    header('Location: index.php?controller=superadmin');
                    exit;
                } else {
                    $_SESSION['id_cliente'] = $user['id_cliente'];
                    $_SESSION['usuario'] = $user['usuario'];
                    $_SESSION['id_usuario'] = $user['id'];
                    $_SESSION['correo'] = $user['correo'];
                    $_SESSION['rol'] = $user['rol'];
                    $_SESSION['id_establecimiento'] = $user['id_establecimiento'];
                    $_SESSION['is_super_admin'] = false;
                    $_SESSION['ultima_actividad'] = time();
                    header('Location: index.php?controller=home');
                    exit;
                }
            } else {
                $this->renderLoginView("Usuario o contraseña incorrectos");
            }
        } else {
            $this->renderLoginView();
        }
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $access_token = trim($_POST['access_token'] ?? '');
            $id_establecimiento = intval($_POST['id_establecimiento'] ?? 0);
            $rol = trim($_POST['rol'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';
            $confirmar = $_POST['confirmar_contrasena'] ?? '';

            $token = AccessToken::obtenerPorHash($access_token);
            if (!$token || $token['estado'] != 1 || ($token['date_expired'] && strtotime($token['date_expired']) < time())) {
                $error = 'El código de acceso no es válido o ya fue usado.';
                $contenido = 'view/components/register.php';
                require 'view/layout.php';
                return;
            }

            if (!$usuario || !$correo || !$contrasena || !$confirmar) {
                $error = 'Todos los campos son obligatorios.';
                $contenido = 'view/components/register.php';
                require 'view/layout.php';
                return;
            }
            if ($contrasena !== $confirmar) {
                $error = 'Las contraseñas no coinciden.';
                $contenido = 'view/components/register.php';
                require 'view/layout.php';
                return;
            }

            $hashed_password = password_hash($contrasena, PASSWORD_BCRYPT);
            $ok = Usuario::insertar(
                $usuario,
                $correo,
                $rol,
                $id_establecimiento,
                1,
                $token['id_cliente'],
                $hashed_password,
                $usuario
            );

            if ($ok) {
                AccessToken::marcarComoUsado(
                    $token['id_token'],
                    $usuario,
                    $_SERVER['REMOTE_ADDR']
                );
                header('Location: index.php?controller=auth&action=login&success=Cuenta creada');
                exit;
            } else {
                $error = 'No se pudo crear el usuario.';
                $contenido = 'view/components/register.php';
                require 'view/layout.php';
                return;
            }
        } else {
            $contenido = 'view/components/register.php';
            require 'view/layout.php';
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
            header('Location: index.php?controller=auth&action=login');
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
