<?php
require_once 'model/Usuario.php';
require_once 'model/Cliente.php';

class SuperAdminController
{
    public function __construct()
    {
        $this->verificarSuperAdmin();
    }

    private function verificarSuperAdmin()
    {
        $esSuperAdmin = (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true) ||
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

        $clientes = Cliente::obtenerClientesConEstablecimientos($limit, $offset, $sort, $dir);
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

    public function salirAccesoDirecto()
    {
        if (!isset($_SESSION['superadmin_mode']) || !$_SESSION['superadmin_mode']) {
            header('Location: index.php?controller=superadmin&action=index');
            exit;
        }

        if (isset($_SESSION['superadmin_original'])) {
            $original = $_SESSION['superadmin_original'];

            $_SESSION['user_id'] = $original['user_id'];
            $_SESSION['id_usuario'] = $original['id_usuario'];
            $_SESSION['rol'] = $original['rol'];
            $_SESSION['establecimiento_id'] = $original['establecimiento_id'];
            $_SESSION['id_cliente'] = $original['id_cliente'];
            $_SESSION['is_super_admin'] = $original['is_super_admin'];
        }

        unset($_SESSION['superadmin_mode']);
        unset($_SESSION['acting_as_establecimiento']);
        unset($_SESSION['superadmin_original']);

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

    private function obtenerEstablecimientoPorId($id_establecimiento, $id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM establecimiento WHERE id = ? AND id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_establecimiento, $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function accesoDirectoEstablecimiento()
    {
        $id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : 0;
        $id_establecimiento = isset($_GET['id_establecimiento']) ? intval($_GET['id_establecimiento']) : 0;

        if ($id_cliente <= 0 || $id_establecimiento <= 0) {
            $_SESSION['error'] = 'Parámetros inválidos';
            header('Location: index.php?controller=superadmin&action=clientes');
            exit;
        }

        $establecimiento = $this->obtenerEstablecimientoPorId($id_establecimiento, $id_cliente);
        if (!$establecimiento) {
            $_SESSION['error'] = 'Establecimiento no encontrado o no pertenece al cliente';
            header('Location: index.php?controller=superadmin&action=clientes');
            exit;
        }

        // Verificar que el cliente existe
        $cliente = $this->obtenerClientePorId($id_cliente);
        if (!$cliente) {
            $_SESSION['error'] = 'Cliente no encontrado';
            header('Location: index.php?controller=superadmin&action=clientes');
            exit;
        }

        // Guardar información original del superadmin
        $_SESSION['superadmin_original'] = [
            'user_id' => $_SESSION['user_id'] ?? null,
            'id_usuario' => $_SESSION['id_usuario'] ?? null,
            'rol' => $_SESSION['rol'] ?? null,
            'establecimiento_id' => $_SESSION['establecimiento_id'] ?? null,
            'id_cliente' => $_SESSION['id_cliente'] ?? null,
            'is_super_admin' => $_SESSION['is_super_admin'] ?? null
        ];

        // Establecer el modo superadmin manteniendo el ID del superadmin
        $_SESSION['superadmin_mode'] = true;
        $_SESSION['acting_as_establecimiento'] = $id_establecimiento;
        $_SESSION['id_establecimiento'] = $id_establecimiento; // Corregido: usar id_establecimiento
        $_SESSION['establecimiento_id'] = $id_establecimiento; // Mantener por compatibilidad
        $_SESSION['id_cliente'] = $id_cliente;
        // MANTENER el ID del SuperAdmin como usuario activo
        // $_SESSION['id_usuario'] y $_SESSION['user_id'] se mantienen igual
        $_SESSION['rol'] = 'SuperAdmin'; // Mantener rol de superadmin

        header('Location: index.php?controller=home&action=dashboard');
        exit;
    }
}
