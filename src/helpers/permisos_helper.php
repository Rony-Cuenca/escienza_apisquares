<?php

/**
 * FUNCIONES DE PERMISOS UNIFICADAS Y CONSISTENTES
 * Sistema refactorizado para roles claros y permisos específicos
 */

// Evitar redeclaración de funciones
if (!function_exists('esSuperAdmin')) {
    function esSuperAdmin()
    {
        $rol = $_SESSION['rol'] ?? '';
        $is_super_admin = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;
        return $rol === 'SuperAdmin' || $is_super_admin;
    }
}

if (!function_exists('esAdministrador')) {
    function esAdministrador()
    {
        $rol = $_SESSION['rol'] ?? '';
        return $rol === 'Administrador';
    }
}

if (!function_exists('esContador')) {
    function esContador()
    {
        $rol = $_SESSION['rol'] ?? '';
        return $rol === 'Contador';
    }
}

if (!function_exists('esVendedor')) {
    function esVendedor()
    {
        $rol = $_SESSION['rol'] ?? '';
        return $rol === 'Vendedor';
    }
}

// PERMISOS ESPECÍFICOS POR MÓDULO

// === PERMISOS DE USUARIOS ===
if (!function_exists('puedeGestionarUsuarios')) {
    function puedeGestionarUsuarios()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('puedeCrearUsuarios')) {
    function puedeCrearUsuarios()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('puedeEditarUsuarios')) {
    function puedeEditarUsuarios()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('puedeCambiarEstadoUsuarios')) {
    function puedeCambiarEstadoUsuarios()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('puedeGenerarCodigosAcceso')) {
    function puedeGenerarCodigosAcceso()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

// === PERMISOS DE ESTABLECIMIENTOS ===
if (!function_exists('puedeGestionarEstablecimientos')) {
    function puedeGestionarEstablecimientos()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('puedeCrearEstablecimientos')) {
    function puedeCrearEstablecimientos()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('puedeEditarEstablecimientos')) {
    function puedeEditarEstablecimientos()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('puedeSincronizarEstablecimientos')) {
    function puedeSincronizarEstablecimientos()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('puedeCambiarEstadoEstablecimientos')) {
    function puedeCambiarEstadoEstablecimientos()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

// === PERMISOS DE REPORTES ===
if (!function_exists('puedeGenerarReportes')) {
    function puedeGenerarReportes()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('puedeExportarReportes')) {
    function puedeExportarReportes()
    {
        return esSuperAdmin() || esAdministrador() || esContador();
    }
}

if (!function_exists('puedeVerReportes')) {
    function puedeVerReportes()
    {
        return esSuperAdmin() || esAdministrador() || esContador();
    }
}

// === PERMISOS DE CUADRES ===
if (!function_exists('puedeHacerCuadres')) {
    function puedeHacerCuadres()
    {
        return esSuperAdmin() || esAdministrador() || esContador();
    }
}

if (!function_exists('puedeCargarArchivos')) {
    function puedeCargarArchivos()
    {
        return esSuperAdmin() || esAdministrador() || esContador();
    }
}

// === FUNCIONES AUXILIARES ===
if (!function_exists('obtenerUsuarioActualSeguro')) {
    function obtenerUsuarioActualSeguro()
    {
        return $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;
    }
}

if (!function_exists('obtenerRolActual')) {
    function obtenerRolActual()
    {
        return $_SESSION['rol'] ?? '';
    }
}

if (!function_exists('tieneAccesoCompleto')) {
    function tieneAccesoCompleto()
    {
        return esSuperAdmin() || esAdministrador();
    }
}

if (!function_exists('soloLectura')) {
    function soloLectura()
    {
        return esContador();
    }
}

if (!function_exists('verificarPermiso')) {
    function verificarPermiso($permiso)
    {
        switch ($permiso) {
            case 'gestionar_usuarios':
                return puedeGestionarUsuarios();
            case 'gestionar_establecimientos':
                return puedeGestionarEstablecimientos();
            case 'generar_reportes':
                return puedeGenerarReportes();
            case 'hacer_cuadres':
                return puedeHacerCuadres();
            case 'acceso_completo':
                return tieneAccesoCompleto();
            default:
                return false;
        }
    }
}

if (!function_exists('obtenerContextoUsuario')) {
    function obtenerContextoUsuario()
    {
        return [
            'id_usuario' => obtenerUsuarioActualSeguro(),
            'rol' => obtenerRolActual(),
            'es_superadmin' => esSuperAdmin(),
            'es_administrador' => esAdministrador(),
            'es_contador' => esContador(),
            'es_vendedor' => esVendedor(),
            'acceso_completo' => tieneAccesoCompleto(),
            'solo_lectura' => soloLectura()
        ];
    }
}
