<?php
require_once 'model/Usuario.php';
require_once 'model/Cliente.php';

class SuperAdminController
{
    public function __construct()
    {
        // Solo verificar Super Admin para acciones que no sean volverSuperAdmin
        $action = $_GET['action'] ?? 'index';
        if ($action !== 'volverSuperAdmin') {
            $this->verificarSuperAdmin();
        }
    }

    private function verificarSuperAdmin()
    {
        // Permitir acceso si es Super Admin O si está impersonando (para volverSuperAdmin)
        $esSuperAdmin = (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true) ||
                       (isset($_SESSION['superadmin_original']) && $_SESSION['impersonating'] === true) ||
                       (isset($_SESSION['rol']) && $_SESSION['rol'] === 'SuperAdmin');
        
        if (!$esSuperAdmin) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    public function index()
    {
        $totalClientes = Usuario::contarTodosLosClientes();
        $totalUsuarios = $this->contarTodosLosUsuarios();
        $clientesRecientes = $this->obtenerClientesRecientes(5);
        
        $contenido = 'view/components/superadmin_dashboard.php';
        require 'view/layout.php';
    }

    public function clientes()
    {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        $sort = $_GET['sort'] ?? 'razon_social';
        $dir = ($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        
        $clientes = Usuario::obtenerTodosLosClientes($limit, $offset, $sort, $dir);
        $total = Usuario::contarTodosLosClientes();
        
        $contenido = 'view/components/superadmin_clientes.php';
        require 'view/layout.php';
    }

    public function verCliente()
    {
        $id_cliente = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($id_cliente <= 0) {
            header('Location: index.php?controller=superadmin&action=clientes');
            exit;
        }
        
        $cliente = $this->obtenerClientePorId($id_cliente);
        if (!$cliente) {
            header('Location: index.php?controller=superadmin&action=clientes');
            exit;
        }
        
        $establecimientos = $this->obtenerEstablecimientosPorCliente($id_cliente);
        $usuarios = $this->obtenerUsuariosPorCliente($id_cliente);
        
        $contenido = 'view/components/superadmin_cliente_detalle.php';
        require 'view/layout.php';
    }

    public function impersonarCliente()
    {
        $id_cliente = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($id_cliente <= 0) {
            header('Location: index.php?controller=superadmin&action=clientes');
            exit;
        }
        
        $cliente = $this->obtenerClientePorId($id_cliente);
        if (!$cliente) {
            header('Location: index.php?controller=superadmin&action=clientes');
            exit;
        }
        
        // Buscar un usuario administrador del cliente
        $usuarioAdmin = $this->obtenerAdminCliente($id_cliente);
        if (!$usuarioAdmin) {
            header('Location: index.php?controller=superadmin&action=clientes&error=No hay administradores para este cliente');
            exit;
        }
        
        // Guardar datos de super admin en la sesión
        $_SESSION['superadmin_original'] = [
            'id_usuario' => $_SESSION['id_usuario'],
            'usuario' => $_SESSION['usuario'],
            'correo' => $_SESSION['correo'],
            'rol' => $_SESSION['rol'],
            'id_cliente' => $_SESSION['id_cliente'],
            'id_establecimiento' => $_SESSION['id_establecimiento']
        ];
        
        // Cambiar sesión al cliente
        $_SESSION['id_cliente'] = $usuarioAdmin['id_cliente'];
        $_SESSION['usuario'] = $usuarioAdmin['usuario'];
        $_SESSION['id_usuario'] = $usuarioAdmin['id'];
        $_SESSION['correo'] = $usuarioAdmin['correo'];
        $_SESSION['rol'] = $usuarioAdmin['rol'];
        $_SESSION['id_establecimiento'] = $usuarioAdmin['id_establecimiento'];
        $_SESSION['is_super_admin'] = false;
        $_SESSION['impersonating'] = true;
        
        header('Location: index.php?controller=home');
        exit;
    }

    public function volverSuperAdmin()
    {
        // Verificar que hay datos de super admin guardados
        if (!isset($_SESSION['superadmin_original'])) {
            // Si no hay datos guardados, intentar determinar si era super admin por el rol
            if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'SuperAdmin') {
                $_SESSION['is_super_admin'] = true;
                unset($_SESSION['impersonating']);
                header('Location: index.php?controller=superadmin&action=index');
                exit;
            }
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $original = $_SESSION['superadmin_original'];
        
        // Limpiar datos de impersonación primero
        unset($_SESSION['superadmin_original']);
        unset($_SESSION['impersonating']);
        
        // Restaurar sesión de super admin
        $_SESSION['id_cliente'] = $original['id_cliente'];
        $_SESSION['usuario'] = $original['usuario'];
        $_SESSION['id_usuario'] = $original['id_usuario'];
        $_SESSION['correo'] = $original['correo'];
        $_SESSION['rol'] = $original['rol'];
        $_SESSION['id_establecimiento'] = $original['id_establecimiento'];
        $_SESSION['is_super_admin'] = true;
        
        header('Location: index.php?controller=superadmin&action=index');
        exit;
    }

    private function contarTodosLosUsuarios()
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE id_cliente > 0";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    private function obtenerClientesRecientes($limit = 5)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM cliente ORDER BY date_create DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function obtenerClientePorId($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM cliente WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    private function obtenerEstablecimientosPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM establecimiento WHERE id_cliente = ? ORDER BY etiqueta";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function obtenerUsuariosPorCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT u.*, e.etiqueta as establecimiento_nombre 
                FROM usuario u 
                LEFT JOIN establecimiento e ON u.id_establecimiento = e.id 
                WHERE u.id_cliente = ? 
                ORDER BY u.usuario";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function obtenerAdminCliente($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM usuario WHERE id_cliente = ? AND rol = 'Administrador' AND estado = 1 LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
