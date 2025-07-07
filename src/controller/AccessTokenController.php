<?php
require_once 'model/AccessToken.php';
require_once __DIR__ . '/../model/Establecimiento.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AccessTokenController
{
    public function index()
    {
        $this->verificarSesion();
        $id_establecimiento = $_SESSION['id_establecimiento'];
        $tokens = AccessToken::listar(['id_establecimiento' => $id_establecimiento]);
        $contenido = 'view/components/access_token.php';
        require 'view/layout.php';
    }

    public function generar()
    {
        $this->verificarSesion();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        header('Content-Type: application/json');

        if (empty($input['rol'])) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            return;
        }

        $payload = [
            'id_establecimiento' => $_SESSION['id_establecimiento'],
            'rol' => $input['rol'],
            'rand' => rand(1000, 9999)
        ];
        $secret = 'ESCIENZA2025';
        $jwt = \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');

        $date_expired = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $data = [
            'id_cliente'      => $_SESSION['id_cliente'],
            'id_establecimiento'     => $_SESSION['id_establecimiento'],
            'rol'             => $input['rol'],
            'estado'          => 1,
            'hashcode'        => $jwt,
            'id_user_create'  => $_SESSION['id_usuario'],
            'user_create'     => $_SESSION['usuario'],
            'user_update'     => $_SESSION['usuario'],
            'date_expired'    => $date_expired,
            'comentario'      => ''
        ];

        $ok = AccessToken::insertar($data);

        if ($ok) {
            echo json_encode(['success' => true, 'codigo' => $jwt]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar el código']);
        }
    }

    public function revocar()
    {
        $this->verificarSesion();
        $id_token = intval($_POST['id_token'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');
        header('Content-Type: application/json');
        if ($id_token > 0) {
            $ok = AccessToken::revocar($id_token, $comentario);
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
        }
        exit;
    }

    public function marcarComoUsado()
    {
        $this->verificarSesion();
        $id_token = intval($_POST['id_token'] ?? 0);
        $user_used = $_SESSION['usuario'] ?? 'desconocido';
        $ip_used = $_SERVER['REMOTE_ADDR'] ?? '';
        header('Content-Type: application/json');
        if ($id_token > 0) {
            $ok = AccessToken::marcarComoUsado($id_token, $user_used, $ip_used);
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
        }
        exit;
    }

    public function validar()
    {
        $hashcode = trim($_POST['hashcode'] ?? '');
        $token = \AccessToken::obtenerPorHash($hashcode);
        header('Content-Type: application/json');
        if (!$token) {
            echo json_encode(['success' => false, 'error' => 'Código no válido']);
            exit;
        }
        if ($token['estado'] != 1) {
            echo json_encode(['success' => false, 'error' => 'El código ya fue usado, revocado o expiró']);
            exit;
        }
        if ($token['date_expired'] && strtotime($token['date_expired']) < time()) {
            echo json_encode(['success' => false, 'error' => 'El código ha expirado']);
            exit;
        }

        try {
            $secret = 'ESCIENZA2025';
            $decoded = \Firebase\JWT\JWT::decode($hashcode, new \Firebase\JWT\Key($secret, 'HS256'));
            $id_establecimiento = $decoded->id_establecimiento ?? $token['id_establecimiento'];
            $id_cliente = $token['id_cliente'] ?? null;
            $establecimiento = \Establecimiento::obtenerPorId($id_establecimiento, $id_cliente);
            $nombre_establecimiento = $establecimiento ? $establecimiento['etiqueta'] : $id_establecimiento;
            echo json_encode([
                'success' => true,
                'token' => $token,
                'decoded' => $decoded,
                'nombre_establecimiento' => $nombre_establecimiento
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Código inválido o alterado']);
        }
        exit;
    }

    private function limpiarDatos($data)
    {
        return [
            'rol' => trim(strip_tags($data['rol'] ?? '')),
            'comentario' => trim(strip_tags($data['comentario'] ?? '')),
            'date_expired' => trim($data['date_expired'] ?? '')
        ];
    }

    private function validarDatos($datos)
    {
        if (empty($datos['rol'])) {
            return 'El rol es obligatorio';
        }
        if (!empty($datos['date_expired']) && strtotime($datos['date_expired']) < time()) {
            return 'La fecha de expiración debe ser futura';
        }
        return '';
    }

    private function verificarSesion()
    {
        if (!isset($_SESSION['id_cliente']) || !isset($_SESSION['id_establecimiento']) || ($_SESSION['rol'] ?? '') !== 'Administrador') {
            header('Location: index.php?controller=auth&action=login&error=No autorizado');
            exit;
        }
    }
}
