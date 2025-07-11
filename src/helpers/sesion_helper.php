<?php
/**
 * Helper para manejar sesiones y obtener información del usuario actual
 * Unifica la lógica entre modo normal y modo superadmin
 */
class SesionHelper 
{
    /**
     * Obtiene el ID del usuario actual considerando el modo de acceso
     * @return string|null ID del usuario o null si no se encuentra
     */
    public static function obtenerUsuarioActual() 
    {
        // Tanto en modo normal como en modo superadmin directo,
        // siempre debería haber un id_usuario válido
        return $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtiene el ID del cliente actual
     * @return int ID del cliente
     */
    public static function obtenerClienteActual() 
    {
        // En ambos modos (normal y superadmin directo), usar la variable de sesión
        if (isset($_SESSION['id_cliente']) && $_SESSION['id_cliente'] !== null && $_SESSION['id_cliente'] !== '') {
            return (int)$_SESSION['id_cliente'];
        }
        
        // Fallback: obtener el cliente del usuario en modo normal
        $userId = self::obtenerUsuarioActual();
        if ($userId) {
            require_once __DIR__ . '/../model/Usuario.php';
            $usuario = Usuario::obtenerId($userId);
            return $usuario ? (int)$usuario['id_cliente'] : 0;
        }
        
        return 0;
    }

    /**
     * Verifica si estamos en modo SuperAdmin
     * @return bool
     */
    public static function esModoSuperAdmin() 
    {
        return isset($_SESSION['superadmin_mode']) && $_SESSION['superadmin_mode'] === true;
    }

    /**
     * Verifica si el usuario es SuperAdmin
     * @return bool
     */
    public static function esSuperAdmin() 
    {
        return (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true) ||
               (isset($_SESSION['rol']) && $_SESSION['rol'] === 'SuperAdmin');
    }
    
    /**
     * Obtener nombre del usuario actual
     * @return string
     */
    public static function obtenerNombreUsuario()
    {
        return $_SESSION['usuario'] ?? 'Desconocido';
    }
    
    /**
     * Obtener rol del usuario actual
     * @return string
     */
    public static function obtenerRolActual()
    {
        return $_SESSION['rol'] ?? '';
    }
    
    /**
     * Obtener ID del establecimiento actual
     * @return int|null
     */
    public static function obtenerEstablecimientoActual()
    {
        // En modo SuperAdmin directo, usar el establecimiento objetivo
        if (self::esModoSuperAdmin() && isset($_SESSION['acting_as_establecimiento'])) {
            return $_SESSION['acting_as_establecimiento'];
        }
        
        // Modo normal: usar la variable de sesión estándar
        return $_SESSION['id_establecimiento'] ?? $_SESSION['establecimiento_id'] ?? null;
    }

    /**
     * Obtiene información completa del contexto actual
     * @return array
     */
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
