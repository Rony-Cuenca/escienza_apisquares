<?php
require_once 'model/Usuario.php';

class UsuarioController
{
    public function index()
    {
        $this->verificarSesion();
        $id_establecimiento = $_SESSION['id_establecimiento'];
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        $sort = $_GET['sort'] ?? 'establecimiento';
        $dir = ($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $usuarios = Usuario::obtenerPaginadoPorEstablecimiento($id_establecimiento, $limit, $offset, $sort, $dir);
        $total = Usuario::contarPorEstablecimiento($id_establecimiento);
        $establecimientos = Usuario::obtenerEstablecimientosPorCliente($_SESSION['id_cliente']);
        $correo_cliente = Usuario::obtenerCorreoCliente($_SESSION['id_cliente']);
        $nombre_establecimiento_logueado = Usuario::obtenerNombreEstablecimiento($_SESSION['id_establecimiento']);
        $contenido = 'view/components/usuario.php';
        require 'view/layout.php';
    }

    public function crear()
    {
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
            $es_propio_usuario = ($id_usuario == $_SESSION['id_usuario']);
            
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

            // Si es el propio usuario, mantener ciertos valores de la sesión
            if ($es_propio_usuario) {
                // Mantener el rol y establecimiento actuales si se está editando a sí mismo
                $datos['rol'] = $_SESSION['rol'];
                $datos['id_establecimiento'] = $_SESSION['id_establecimiento'];
            }

            Usuario::actualizar(
                $id_usuario,
                $datos['usuario'],
                $datos['correo'],
                $datos['rol'],
                $datos['id_establecimiento'],
                intval($datos['estado']),
                $_SESSION['id_cliente'],
                $_SESSION['usuario'],
                $hashed_password
            );

            if ($es_propio_usuario) {
                $_SESSION['usuario'] = $datos['usuario'];
                $_SESSION['correo'] = $datos['correo'];
                // No actualizar rol ni establecimiento en sesión ya que no los cambiamos
            }
            header('Location: index.php?controller=usuario');
            exit;
        }
    }

    public function cambiarEstado($id, $estado)
    {
        $id = intval($id);
        $estado = intval($estado);

        if ($id == $_SESSION['id_usuario']) {
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
        // Si hay campos hidden (para el propio usuario), usar esos valores
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
        
        // Validaciones básicas
        if (empty($datos['usuario'])) {
            return 'El nombre de usuario es obligatorio';
        }
        
        if (empty($datos['correo'])) {
            return 'El correo es obligatorio';
        }

        // Si no es el propio usuario, validar rol y establecimiento
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
        
        // Validación de contraseña solo si se proporciona
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
