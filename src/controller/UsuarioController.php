<?php
require_once 'model/Usuario.php';

class UsuarioController
{
    public function index()
    {
        $this->verificarSesion();

        $id_cliente = $_SESSION['id_cliente'];
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        $sort = $_GET['sort'] ?? 'sucursal';
        $dir = ($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $usuarios = Usuario::obtenerPaginado($id_cliente, $limit, $offset, $sort, $dir);
        $total = Usuario::contarPorCliente($id_cliente);
        $sucursales = Usuario::obtenerSucursalesPorCliente($id_cliente);
        $correo_cliente = Usuario::obtenerCorreoCliente($id_cliente);
        $contenido = 'view/components/usuario.php';
        require 'view/layout.php';
    }

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->limpiarDatos($_POST);
            $error = $this->validarDatos($datos);

            if ($error) {
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            if (Usuario::existeUsuario($datos['usuario'])) {
                $contenido = __DIR__ . '/../view/components/error.php';
                require 'view/layout.php';
                exit;
            }

            $hashed_password = password_hash($datos['contraseña'], PASSWORD_BCRYPT);
            Usuario::insertar(
                $datos['usuario'],
                $datos['rol'],
                $datos['id_sucursal'],
                1,
                $_SESSION['id_cliente'],
                $hashed_password,
                $_SESSION['usuario']
            );
            header('Location: index.php?controller=usuario');
            exit;
        }
    }

    public function editar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->limpiarDatos($_POST);
            $id_usuario = intval($datos['id_usuario']);
            $error = $this->validarDatos($datos, true);

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

            $hashed_password = null;
            if (!empty($datos['contraseña']) || !empty($datos['confirmar_contraseña'])) {
                if ($datos['contraseña'] !== $datos['confirmar_contraseña']) {
                    $contenido = __DIR__ . '/../view/components/error.php';
                    require 'view/layout.php';
                    exit;
                }
                $hashed_password = password_hash($datos['contraseña'], PASSWORD_BCRYPT);
            }

            Usuario::actualizar(
                $id_usuario,
                $datos['usuario'],
                $datos['rol'],
                $datos['id_sucursal'],
                intval($datos['estado']),
                $_SESSION['id_cliente'],
                $hashed_password,
                $_SESSION['usuario']
            );
            header('Location: index.php?controller=usuario');
            exit;
        }
    }

    public function cambiarEstado($id, $estado)
    {
        $id = intval($id);
        $estado = intval($estado);

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

    private function limpiarDatos($data)
    {
        return [
            'id_usuario' => isset($data['id_usuario']) ? intval($data['id_usuario']) : 0,
            'usuario' => trim(strip_tags($data['usuario'] ?? '')),
            'rol' => trim(strip_tags($data['rol'] ?? '')),
            'id_sucursal' => intval($data['id_sucursal'] ?? 0),
            'estado' => isset($data['estado']) ? intval($data['estado']) : 1,
            'contraseña' => $data['contraseña'] ?? '',
            'confirmar_contraseña' => $data['confirmar_contraseña'] ?? ''
        ];
    }

    private function validarDatos($datos, $esEdicion = false)
    {
        if ($esEdicion && $datos['id_usuario'] <= 0) {
            return 'ID de usuario inválido';
        }
        if (empty($datos['usuario']) || empty($datos['rol']) || $datos['id_sucursal'] <= 0) {
            return 'Todos los campos son obligatorios';
        }

        if (preg_match('/[áéíóúÁÉÍÓÚ]/u', $datos['usuario'])) {
            return 'El nombre de usuario no debe contener tildes';
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
        if (!isset($_SESSION['id_cliente'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }
}
