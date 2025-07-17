<?php

class SesionHelper
{
    public static function obtenerUsuarioActual()
    {
        return $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;
    }

    public static function obtenerClienteActual()
    {
        if (isset($_SESSION['id_cliente']) && $_SESSION['id_cliente'] !== null && $_SESSION['id_cliente'] !== '') {
            return (int)$_SESSION['id_cliente'];
        }
        $userId = self::obtenerUsuarioActual();
        if ($userId) {
            require_once __DIR__ . '/../model/Usuario.php';
            $usuario = Usuario::obtenerId($userId);
            return $usuario ? (int)$usuario['id_cliente'] : 0;
        }

        return 0;
    }

    public static function esModoSuperAdmin()
    {
        return isset($_SESSION['superadmin_mode']) && $_SESSION['superadmin_mode'] === true;
    }

    public static function esSuperAdmin()
    {
        return (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true) ||
            (isset($_SESSION['rol']) && $_SESSION['rol'] === 'SuperAdmin');
    }

    public static function obtenerNombreUsuario()
    {
        return $_SESSION['usuario'] ?? 'Desconocido';
    }

    public static function obtenerRolActual()
    {
        return $_SESSION['rol'] ?? '';
    }

    public static function obtenerEstablecimientoActual()
    {
        if (self::esModoSuperAdmin() && isset($_SESSION['acting_as_establecimiento'])) {
            return $_SESSION['acting_as_establecimiento'];
        }

        return $_SESSION['id_establecimiento'] ?? $_SESSION['establecimiento_id'] ?? null;
    }

    public static function obtenerContexto()
    {
        return [
            'es_superadmin' => self::esSuperAdmin(),
            'es_modo_directo' => self::esModoSuperAdmin(),
            'usuario_id' => self::obtenerUsuarioActual(),
            'cliente_id' => self::obtenerClienteActual(),
            'establecimiento_id' => $_SESSION['establecimiento_id'] ?? 0
        ];
    }
}
