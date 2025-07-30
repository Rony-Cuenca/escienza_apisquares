<?php
require_once 'model/Usuario.php';
require_once __DIR__ . '/../helpers/permisos_helper.php';
require_once 'helpers/sesion_helper.php';

class UsuarioController
{
    private function verificarPermisosCompletos()
    {
        if (!tieneAccesoCompleto()) {
            $_SESSION['mensaje'] = "No tienes permisos para realizar esta acción.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: index.php?controller=home&action=index");
            exit();
        }
    }

    private function verificarPermisosGestion()
    {
        if (!puedeGestionarUsuarios()) {
            $_SESSION['mensaje'] = "No tienes permisos para gestionar usuarios.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: index.php?controller=home&action=index");
            exit();
        }
    }

    private function obtenerEstablecimientoActual()
    {
        return SesionHelper::obtenerEstablecimientoActual();
    }

    public function index()
    {
        $this->verificarSesion();

        $id_cliente = SesionHelper::obtenerClienteActual();
        $id_establecimiento = $this->obtenerEstablecimientoActual();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        $sort = $_GET['sort'] ?? 'establecimiento';
        $dir = ($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        if (Usuario::esSuperAdmin()) {
            $usuarios = Usuario::obtenerPaginadoPorCliente($id_cliente, $limit, $offset, $sort, $dir);
            $total = Usuario::contarPorCliente($id_cliente);
        } else {
            if (!$id_establecimiento) {
                header('Location: index.php?controller=home&action=index');
                exit;
            }
            $usuarios = Usuario::obtenerPaginadoPorEstablecimiento($id_establecimiento, $limit, $offset, $sort, $dir);
            $total = Usuario::contarPorEstablecimiento($id_establecimiento);
        }

        $establecimientos = Usuario::obtenerEstablecimientosPorCliente($id_cliente);
        $correo_cliente = Usuario::obtenerCorreoCliente($id_cliente);
        $nombre_establecimiento_logueado = Usuario::obtenerNombreEstablecimiento($id_establecimiento);

        $contenido = 'view/components/usuario.php';
        require 'view/layout.php';
    }

    public function crear()
    {
        $this->verificarSesion();
        $this->verificarPermisosCompletos();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->limpiarDatos($_POST);

            $error = $this->validarDatos($datos, false, false);
            if ($error) {
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            $id_usuario = 0;

            if (Usuario::existeUsuario($datos['usuario'])) {
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            if (Usuario::existeCorreo($datos['correo'], $id_usuario)) {
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            $hashed_password = password_hash($datos['contraseña'], PASSWORD_BCRYPT);

            Usuario::insertar(
                $datos['usuario'],
                $datos['correo'],
                $datos['rol'],
                $datos['id_establecimiento'],
                1,
                SesionHelper::obtenerClienteActual(),
                $hashed_password,
                SesionHelper::obtenerNombreUsuario()
            );
            header('Location: index.php?controller=usuario');
            exit;
        }
    }

    public function editar()
    {
        $this->verificarSesion();
        $this->verificarPermisosCompletos();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->limpiarDatos($_POST);

            $id_usuario = intval($datos['id_usuario']);
            $es_propio_usuario = ($id_usuario == obtenerUsuarioActualSeguro());

            $error = $this->validarDatos($datos, true, $es_propio_usuario);

            if ($error) {
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            if (Usuario::existeUsuario($datos['usuario'], $id_usuario)) {
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            if (Usuario::existeCorreo($datos['correo'], $id_usuario)) {
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            $hashed_password = null;
            if (!empty($datos['contraseña']) || !empty($datos['confirmar_contraseña'])) {
                if ($datos['contraseña'] !== $datos['confirmar_contraseña']) {
                    $contenido = __DIR__ . '/../view/components/error.php';
                    require 'view/layout.php';
                    exit;
                }
                $hashed_password = password_hash($datos['contraseña'], PASSWORD_BCRYPT);
            }

            if ($es_propio_usuario) {
                $datos['rol'] = $_SESSION['rol'];
                $datos['id_establecimiento'] = SesionHelper::obtenerEstablecimientoActual(); // Usar SesionHelper
            }

            Usuario::actualizar(
                $id_usuario,
                $datos['usuario'],
                $datos['correo'],
                $datos['rol'],
                $datos['id_establecimiento'],
                intval($datos['estado']),
                SesionHelper::obtenerClienteActual(),
                SesionHelper::obtenerNombreUsuario(),
                $hashed_password
            );

            if ($es_propio_usuario) {
                $_SESSION['usuario'] = $datos['usuario'];
                $_SESSION['correo'] = $datos['correo'];
            }
            header('Location: index.php?controller=usuario');
            exit;
        }
    }

    public function cambiarEstado($id, $estado)
    {
        $this->verificarSesion();
        $this->verificarPermisosCompletos();

        $id = intval($id);
        $estado = intval($estado);

        if ($id == obtenerUsuarioActualSeguro()) {
            echo json_encode(['success' => false, 'error' => 'No puedes cambiar el estado de tu propio usuario.']);
            return;
        }

        if ($id <= 0 || !in_array($estado, [1, 2, 3])) {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
            return;
        }

        $resultado = Usuario::cambiarEstado($id, $estado);

        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo cambiar el estado']);
        }
    }

    public function verificarUsuario()
    {
        $usuario = trim($_GET['usuario'] ?? '');
        $id_usuario = intval($_GET['id_usuario'] ?? 0);
        $existe = Usuario::existeUsuario($usuario, $id_usuario);
        echo json_encode(['existe' => $existe]);
        exit;
    }

    public function verificarCorreo()
    {
        $correo = trim($_GET['correo'] ?? '');
        $id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : 0;
        $existe = Usuario::existeCorreo($correo, $id_usuario);
        header('Content-Type: application/json');
        echo json_encode(['existe' => $existe]);
        exit;
    }

    private function limpiarDatos($data)
    {
        $rol = isset($data['rol_hidden']) ? trim(strip_tags($data['rol_hidden'])) : trim(strip_tags($data['rol'] ?? ''));
        $id_establecimiento = isset($data['id_establecimiento_hidden']) ? intval($data['id_establecimiento_hidden']) : intval($data['id_establecimiento'] ?? 0);
        $estado = isset($data['estado_hidden']) ? intval($data['estado_hidden']) : (isset($data['estado']) ? intval($data['estado']) : 1);

        return [
            'id_usuario' => isset($data['id_usuario']) ? intval($data['id_usuario']) : 0,
            'usuario' => trim(strip_tags($data['usuario'] ?? '')),
            'correo' => trim(strip_tags($data['correo'] ?? '')),
            'rol' => $rol,
            'id_establecimiento' => $id_establecimiento,
            'estado' => $estado,
            'contraseña' => $data['contraseña'] ?? '',
            'confirmar_contraseña' => $data['confirmar_contraseña'] ?? ''
        ];
    }

    private function validarDatos($datos, $esEdicion = false, $esPropioUsuario = false)
    {
        if ($esEdicion && $datos['id_usuario'] <= 0) {
            return 'ID de usuario inválido';
        }

        if (empty($datos['usuario'])) {
            return 'El nombre de usuario es obligatorio';
        }

        if (empty($datos['correo'])) {
            return 'El correo es obligatorio';
        }

        if (!$esPropioUsuario) {
            if (empty($datos['rol']) || $datos['id_establecimiento'] <= 0) {
                return 'Todos los campos son obligatorios';
            }
        }

        if (preg_match('/[áéíóúÁÉÍÓÚ]/u', $datos['usuario'])) {
            return 'El nombre de usuario no debe contener tildes';
        }

        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            return 'El correo no es válido';
        }

        if (!$esEdicion && (empty($datos['contraseña']) || empty($datos['confirmar_contraseña']))) {
            return 'La contraseña es obligatoria';
        }

        if (!empty($datos['contraseña']) || !empty($datos['confirmar_contraseña'])) {
            if ($datos['contraseña'] !== $datos['confirmar_contraseña']) {
                return 'Las contraseñas no coinciden';
            }
            $pass = $datos['contraseña'];
            if (strlen($pass) < 8) {
                return 'La contraseña debe tener al menos 8 caracteres';
            }
            if (!preg_match('/\d/', $pass)) {
                return 'La contraseña debe contener al menos un número';
            }
            if (!preg_match('/[\W_]/', $pass)) {
                return 'La contraseña debe contener al menos un símbolo';
            }
            if (preg_match('/[áéíóúÁÉÍÓÚ]/u', $pass)) {
                return 'La contraseña no debe contener tildes';
            }
        }
        return null;
    }

    private function verificarSesion()
    {
        if (!SesionHelper::obtenerClienteActual()) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        if (!puedeGestionarUsuarios()) {
            $_SESSION['error'] = 'No tienes permisos para gestionar usuarios';
            header('Location: index.php?controller=home&action=index');
            exit;
        }
    }
}
