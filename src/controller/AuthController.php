<?php
require_once __DIR__ . '/../model/Usuario.php';

class AuthController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_POST['usuario'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';
            $user = Usuario::autenticar($usuario, $contrasena);
            if ($user) {
                $_SESSION['id_cliente'] = $user['id_cliente'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['id_usuario'] = $user['id'];
                header('Location: index.php');
                exit;
            } else {
                $error = "Usuario o contraseña incorrectos";
                $contenido = 'view/components/login.php';
                require 'view/layout.php';
                exit;
            }
        } else {
            $contenido = 'view/components/login.php';
            require 'view/layout.php';
            exit;
        }
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
}
