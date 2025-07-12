<?php
require_once 'model/Establecimiento.php';
require_once 'config/conexion.php';
require_once 'config/api_config.php';
require_once 'helpers/sesion_helper.php';
require_once __DIR__ . '/../helpers/permisos_helper.php';

class EstablecimientoController
{
    private function verificarSesion()
    {
        if (!SesionHelper::obtenerClienteActual()) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    private function verificarPermisosGestion()
    {
        if (!puedeGestionarEstablecimientos()) {
            $_SESSION['mensaje'] = "No tienes permisos para gestionar establecimientos.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: index.php?controller=home&action=index");
            exit();
        }
    }

    private function verificarPermisosCreacion()
    {
        if (!puedeCrearEstablecimientos()) {
            $this->responseJson(['success' => false, 'error' => 'No tienes permisos para crear establecimientos.']);
        }
    }

    private function verificarPermisosEdicion()
    {
        if (!puedeEditarEstablecimientos()) {
            $this->responseJson(['success' => false, 'error' => 'No tienes permisos para editar establecimientos.']);
        }
    }

    private function verificarPermisosSincronizacion()
    {
        if (!puedeSincronizarEstablecimientos()) {
            $this->responseJson(['success' => false, 'error' => 'No tienes permisos para sincronizar establecimientos.']);
        }
    }

    private function responseJson($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function index()
    {
        $this->verificarSesion();
        $this->verificarPermisosGestion(); // Añadir verificación de permisos

        $id_cliente = SesionHelper::obtenerClienteActual();
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        $sort = $_GET['sort'] ?? 'codigo_establecimiento';
        $dir = ($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        
        $this->asegurarEstablecimientoPrincipal($id_cliente);
        $this->actualizarEtiquetasVacias($id_cliente);

        $establecimientos = Establecimiento::obtenerPorCliente($id_cliente, $limit, $offset, $sort, $dir);
        $total = Establecimiento::contarPorCliente($id_cliente);
        $cliente = Establecimiento::obtenerClientePorId($id_cliente);

        $contenido = 'view/components/establecimiento.php';
        require 'view/layout.php';
    }

    private function asegurarEstablecimientoPrincipal($id_cliente)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM establecimiento WHERE id_cliente = ? AND estado IN (1, 2)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'];

        if ($count == 0) {
            $cliente = Establecimiento::obtenerClientePorId($id_cliente);
            if ($cliente) {
                $datos = [
                    'id_cliente' => $id_cliente,
                    'codigo_establecimiento' => '0000',
                    'tipo_establecimiento' => 'MATRIZ',
                    'etiqueta' => $cliente['razon_social'] ?? 'Establecimiento Principal',
                    'direccion' => $cliente['direccion'] ?? 'Sin dirección',
                    'direccion_completa' => $cliente['direccion'] ?? 'Sin dirección',
                    'departamento' => $cliente['departamento'] ?? '',
                    'provincia' => $cliente['provincia'] ?? '',
                    'distrito' => $cliente['distrito'] ?? '',
                    'user_create' => SesionHelper::obtenerNombreUsuario(),
                    'user_update' => SesionHelper::obtenerNombreUsuario(),
                    'estado' => 1
                ];

                Establecimiento::insertar($datos);
            }
        }
    }

    public function sincronizarEstablecimientos()
    {
        $this->verificarSesion();
        $this->verificarPermisosSincronizacion(); // Añadir verificación de permisos

        $id_cliente = SesionHelper::obtenerClienteActual();
        $cliente = Establecimiento::obtenerClientePorId($id_cliente);

        if (!$cliente) {
            echo json_encode([
                'success' => false,
                'error' => 'Cliente no encontrado'
            ]);
            exit;
        }

        $apiResponse = $this->consultarApiFactiliza($cliente['ruc']);

        if (!$apiResponse['success']) {
            echo json_encode([
                'success' => false,
                'error' => 'Error al consultar SUNAT: ' . $apiResponse['error']
            ]);
            exit;
        }

        $establecimientosCreados = 0;
        $establecimientosActualizados = 0;

        if ($apiResponse['tipo'] === 'con_establecimientos' && isset($apiResponse['establecimientos'])) {
            foreach ($apiResponse['establecimientos'] as $est) {
                $resultado = $this->procesarEstablecimiento($est, $id_cliente);
                if ($resultado['accion'] === 'creado') {
                    $establecimientosCreados++;
                } elseif ($resultado['accion'] === 'actualizado') {
                    $establecimientosActualizados++;
                }
            }
        } else if ($apiResponse['tipo'] === 'info_basica' && isset($apiResponse['data'])) {
            $establecimientoPrincipal = [
                'codigo_establecimiento' => '0000',
                'tipo_establecimiento' => 'MATRIZ',
                'direccion' => $apiResponse['data']['direccion'] ?? '',
                'direccion_completa' => $apiResponse['data']['direccion_completa'] ?? $apiResponse['data']['direccion'] ?? '',
                'departamento' => $apiResponse['data']['departamento'] ?? '',
                'provincia' => $apiResponse['data']['provincia'] ?? '',
                'distrito' => $apiResponse['data']['distrito'] ?? ''
            ];

            $resultado = $this->procesarEstablecimiento($establecimientoPrincipal, $id_cliente);
            if ($resultado['accion'] === 'creado') {
                $establecimientosCreados++;
            } elseif ($resultado['accion'] === 'actualizado') {
                $establecimientosActualizados++;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Sincronización completada. Creados: $establecimientosCreados, Actualizados: $establecimientosActualizados",
            'creados' => $establecimientosCreados,
            'actualizados' => $establecimientosActualizados
        ]);
        exit;
    }

    private function procesarEstablecimiento($datosEstablecimiento, $id_cliente)
    {
        $codigo = $datosEstablecimiento['codigo_establecimiento'] ??
            $datosEstablecimiento['codigo'] ?? '0000';
        $tipo = $datosEstablecimiento['tipo_establecimiento'] ?? 'MATRIZ';
        $direccion = $datosEstablecimiento['direccion'] ?? '';
        $direccion_completa = $datosEstablecimiento['direccion_completa'] ?? $direccion;
        $departamento = $datosEstablecimiento['departamento'] ?? '';
        $provincia = $datosEstablecimiento['provincia'] ?? '';
        $distrito = $datosEstablecimiento['distrito'] ?? '';
        
        // Obtener la razón social del cliente para usar como etiqueta por defecto
        $cliente = Establecimiento::obtenerClientePorId($id_cliente);
        $etiquetaPorDefecto = $cliente['razon_social'] ?? 'Establecimiento';
        
        $establecimientoExistente = Establecimiento::obtenerPorCodigoYCliente($codigo, $id_cliente);

        $datosCompletos = [
            'id_cliente' => $id_cliente,
            'codigo_establecimiento' => $codigo,
            'tipo_establecimiento' => $tipo,
            'etiqueta' => $etiquetaPorDefecto,
            'direccion' => $direccion,
            'direccion_completa' => $direccion_completa,
            'departamento' => $departamento,
            'provincia' => $provincia,
            'distrito' => $distrito,
            'user_create' => SesionHelper::obtenerNombreUsuario(),
            'user_update' => SesionHelper::obtenerNombreUsuario(),
            'estado' => 1
        ];

        if ($establecimientoExistente) {
            // Solo actualizar los datos de SUNAT, preservar la etiqueta existente
            Establecimiento::actualizarPorCodigo($establecimientoExistente['id'], $datosCompletos);
            
            // Si el establecimiento existente no tiene etiqueta, establecer la razón social
            if (empty($establecimientoExistente['etiqueta'])) {
                Establecimiento::actualizarEtiquetaYDireccion(
                    $establecimientoExistente['id'],
                    $etiquetaPorDefecto,
                    $direccion,
                    $id_cliente,
                    SesionHelper::obtenerNombreUsuario()
                );
            }
            
            return ['accion' => 'actualizado', 'codigo' => $codigo];
        } else {
            Establecimiento::insertar($datosCompletos);
            return ['accion' => 'creado', 'codigo' => $codigo];
        }
    }

    public function cambiarEstado($id, $estado)
    {
        $this->verificarSesion();
        
        // Verificar permisos para cambiar estado
        if (!puedeCambiarEstadoEstablecimientos()) {
            $this->responseJson(['success' => false, 'error' => 'No tienes permisos para cambiar el estado de establecimientos.']);
        }

        $id = intval($id);
        $estado = intval($estado);
        $id_cliente = SesionHelper::obtenerClienteActual();

        if ($id <= 0 || !in_array($estado, [1, 2, 3])) {
            $this->responseJson(['success' => false, 'error' => 'Datos inválidos']);
            return;
        }

        $resultado = Establecimiento::cambiarEstado($id, $estado, $id_cliente);

        if ($resultado) {
            $this->responseJson(['success' => true]);
        } else {
            $this->responseJson(['success' => false, 'error' => 'No se pudo cambiar el estado']);
        }
    }

    public function verificarRuc()
    {
        $this->verificarSesion();

        $ruc = trim($_GET['ruc'] ?? '');
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $existe = Establecimiento::existeRuc($ruc, $id);
        header('Content-Type: application/json');
        echo json_encode(['existe' => $existe]);
        exit;
    }

    public function validarRucSunat()
    {
        $this->verificarSesion();

        $ruc = trim($_GET['ruc'] ?? '');

        if (empty($ruc) || !preg_match('/^\d{11}$/', $ruc)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'RUC debe tener 11 dígitos numéricos'
            ]);
            exit;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $existe = Establecimiento::existeRuc($ruc, $id);
        $apiResponse = $this->consultarApiFactiliza($ruc);

        if (!$apiResponse['success']) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $apiResponse['error'],
                'existe_en_bd' => $existe
            ]);
            exit;
        }

        header('Content-Type: application/json');

        if ($apiResponse['tipo'] === 'con_establecimientos') {
            echo json_encode([
                'success' => true,
                'info_basica' => $apiResponse['info_basica'],
                'establecimientos' => $apiResponse['establecimientos'],
                'tipo' => 'con_establecimientos',
                'existe_en_bd' => $existe,
                'message' => 'RUC válido - ' . count($apiResponse['establecimientos']) . ' establecimientos encontrados'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'data' => $apiResponse['data'],
                'tipo' => 'info_basica',
                'existe_en_bd' => $existe,
                'message' => 'RUC válido - Información básica obtenida'
            ]);
        }
        exit;
    }

    public function obtenerEstablecimientosPorRuc()
    {
        $this->verificarSesion();

        $ruc = trim($_GET['ruc'] ?? '');

        if (empty($ruc) || !preg_match('/^\d{11}$/', $ruc)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'RUC debe tener 11 dígitos numéricos',
                'establecimientos' => []
            ]);
            exit;
        }

        $apiResponse = $this->consultarApiFactiliza($ruc);

        if (!$apiResponse['success']) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $apiResponse['error'],
                'establecimientos' => []
            ]);
            exit;
        }

        $establecimientos = [];

        if ($apiResponse['tipo'] === 'con_establecimientos' && isset($apiResponse['establecimientos'])) {
            foreach ($apiResponse['establecimientos'] as $est) {
                $establecimientos[] = [
                    'codigo' => $est['codigo_establecimiento'] ?? '0000',
                    'tipo' => $est['tipo_establecimiento'] ?? 'MATRIZ',
                    'direccion' => $est['direccion'] ?? '',
                    'direccion_completa' => $est['direccion_completa'] ?? $est['direccion'] ?? '',
                    'departamento' => $est['departamento'] ?? '',
                    'provincia' => $est['provincia'] ?? '',
                    'distrito' => $est['distrito'] ?? '',
                    'descripcion' => ($est['tipo_establecimiento'] ?? 'MATRIZ') . ' (' . ($est['codigo_establecimiento'] ?? '0000') . ') - ' . ($est['direccion'] ?? 'Sin dirección')
                ];
            }
        } else if ($apiResponse['tipo'] === 'info_basica' && isset($apiResponse['data'])) {
            $info = $apiResponse['data'];
            $establecimientos[] = [
                'codigo' => '0000',
                'tipo' => 'MATRIZ',
                'direccion' => $info['direccion'] ?? '',
                'direccion_completa' => $info['direccion_completa'] ?? $info['direccion'] ?? '',
                'departamento' => $info['departamento'] ?? '',
                'provincia' => $info['provincia'] ?? '',
                'distrito' => $info['distrito'] ?? '',
                'descripcion' => 'MATRIZ (0000) - ' . ($info['direccion'] ?? 'Sin dirección')
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'establecimientos' => $establecimientos,
            'total' => count($establecimientos),
            'message' => count($establecimientos) . ' establecimientos encontrados'
        ]);
        exit;
    }

    private function consultarApiFactiliza($ruc)
    {
        if (empty(ApiConfig::FACTILIZA_TOKEN)) {
            return [
                'success' => false,
                'error' => 'Token de API no configurado. Contacte al administrador.'
            ];
        }

        $infoBasica = $this->consultarRucInfo($ruc);

        if (!$infoBasica['success']) {
            return $infoBasica;
        }

        $establecimientos = $this->consultarRucEstablecimientos($ruc);

        if ($establecimientos['success'] && !empty($establecimientos['data'])) {
            return [
                'success' => true,
                'info_basica' => $infoBasica['data'],
                'establecimientos' => $establecimientos['data'],
                'tipo' => 'con_establecimientos'
            ];
        } else {
            return [
                'success' => true,
                'data' => $infoBasica['data'],
                'tipo' => 'info_basica'
            ];
        }
    }

    private function consultarRucInfo($ruc)
    {
        $url = ApiConfig::FACTILIZA_API_URL . '/ruc/info/' . $ruc;
        return $this->ejecutarConsultaApi($url);
    }

    private function consultarRucEstablecimientos($ruc)
    {
        $url = ApiConfig::FACTILIZA_API_URL . '/ruc/anexo/' . $ruc;
        return $this->ejecutarConsultaApi($url);
    }

    private function ejecutarConsultaApi($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . ApiConfig::FACTILIZA_TOKEN,
                "Content-Type: application/json"
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return [
                'success' => false,
                'error' => 'Error de conexión con la API: ' . $err
            ];
        }

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['status']) && $data['status'] === 200) {
            return [
                'success' => true,
                'data' => $data['data']
            ];
        } elseif ($httpCode === 404) {
            return [
                'success' => false,
                'error' => 'RUC no encontrado en SUNAT'
            ];
        } elseif ($httpCode === 401) {
            return [
                'success' => false,
                'error' => 'Token de API inválido o expirado'
            ];
        } else {
            $errorMsg = isset($data['message']) ? $data['message'] : 'Error desconocido de la API';
            return [
                'success' => false,
                'error' => 'Error de la API: ' . $errorMsg
            ];
        }
    }

    private function limpiarDatos($data)
    {
        return [
            'id' => isset($data['id']) ? intval($data['id']) : 0,
            'ruc' => trim(strip_tags($data['ruc'] ?? '')),
            'razon_social' => trim(strip_tags($data['razon_social'] ?? '')),
            'direccion' => trim(strip_tags($data['direccion'] ?? '')),
            'id_cliente' => $_SESSION['id_cliente'],
            'estado' => isset($data['estado']) ? intval($data['estado']) : 1
        ];
    }

    private function validarDatos($datos, $esEdicion = false)
    {
        if ($esEdicion && $datos['id'] <= 0) {
            return 'ID de establecimiento inválido';
        }
        if (empty($datos['ruc']) || empty($datos['razon_social'])) {
            return 'RUC y razón social son obligatorios';
        }
        if (!preg_match('/^\d{11}$/', $datos['ruc'])) {
            return 'El RUC debe tener 11 dígitos numéricos';
        }
        return false;
    }

    public function editarEstablecimiento()
    {
        $this->verificarSesion();
        $this->verificarPermisosEdicion(); // Añadir verificación de permisos

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $etiqueta = trim($_POST['etiqueta'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            
            // Usar SesionHelper de manera consistente
            $id_cliente = SesionHelper::obtenerClienteActual();

            if ($id <= 0) {
                $this->responseJson(['success' => false, 'error' => 'ID inválido']);
                return;
            }

            $establecimiento = Establecimiento::obtenerPorId($id, $id_cliente);
            if (!$establecimiento) {
                $this->responseJson(['success' => false, 'error' => 'Establecimiento no encontrado']);
                return;
            }

            $resultado = Establecimiento::actualizarEtiquetaYDireccion(
                $id,
                $etiqueta,
                $direccion,
                $id_cliente,
                SesionHelper::obtenerNombreUsuario()
            );

            if ($resultado) {
                $this->responseJson(['success' => true, 'message' => 'Establecimiento actualizado correctamente']);
            } else {
                $this->responseJson(['success' => false, 'error' => 'No se pudo actualizar el establecimiento']);
            }
        } else {
            $id = intval($_GET['id'] ?? 0);
            $id_cliente = $_SESSION['id_cliente'];

            if ($id <= 0) {
                header('Location: index.php?controller=establecimiento');
                exit;
            }

            $establecimiento = Establecimiento::obtenerPorId($id, $id_cliente);
            if (!$establecimiento) {
                header('Location: index.php?controller=establecimiento');
                exit;
            }

            // Agregar información del cliente para usar como etiqueta por defecto
            $cliente = Establecimiento::obtenerClientePorId($id_cliente);
            $establecimiento['cliente_razon_social'] = $cliente['razon_social'] ?? '';

            header('Content-Type: application/json');
            echo json_encode($establecimiento);
        }
    }

    public function crearEstablecimiento()
    {
        $this->verificarSesion();
        $this->verificarPermisosCreacion(); // Añadir verificación de permisos

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Usar SesionHelper de manera consistente
            $id_cliente = SesionHelper::obtenerClienteActual();

            $datos = [
                'id_cliente' => $id_cliente,
                'codigo_establecimiento' => trim($_POST['codigo_establecimiento'] ?? ''),
                'tipo_establecimiento' => trim($_POST['tipo_establecimiento'] ?? ''),
                'etiqueta' => trim($_POST['etiqueta'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'direccion_completa' => trim($_POST['direccion'] ?? '') . ', ' .
                    trim($_POST['distrito'] ?? '') . ' - ' .
                    trim($_POST['provincia'] ?? '') . ' - ' .
                    trim($_POST['departamento'] ?? ''),
                'departamento' => trim($_POST['departamento'] ?? ''),
                'provincia' => trim($_POST['provincia'] ?? ''),
                'distrito' => trim($_POST['distrito'] ?? ''),
                'user_create' => SesionHelper::obtenerNombreUsuario(),
                'user_update' => SesionHelper::obtenerNombreUsuario()
            ];

            if (empty($datos['codigo_establecimiento'])) {
                $this->responseJson(['success' => false, 'error' => 'El código de establecimiento es obligatorio']);
                return;
            }

            if (empty($datos['tipo_establecimiento'])) {
                $this->responseJson(['success' => false, 'error' => 'El tipo de establecimiento es obligatorio']);
                return;
            }

            if (empty($datos['etiqueta'])) {
                $this->responseJson(['success' => false, 'error' => 'La etiqueta es obligatoria']);
                return;
            }

            if (empty($datos['direccion'])) {
                $this->responseJson(['success' => false, 'error' => 'La dirección es obligatoria']);
                return;
            }

            if (Establecimiento::existeCodigoEstablecimiento($id_cliente, $datos['codigo_establecimiento'])) {
                $this->responseJson(['success' => false, 'error' => 'Ya existe un establecimiento con ese código']);
                return;
            }

            $resultado = Establecimiento::crearEstablecimientoManual($datos);

            if ($resultado['success']) {
                $this->responseJson(['success' => true, 'message' => 'Establecimiento creado correctamente']);
            } else {
                $this->responseJson(['success' => false, 'error' => $resultado['error']]);
            }
        } else {
            $this->responseJson(['success' => false, 'error' => 'Método no permitido']);
        }
    }

    public function verificarCodigoEstablecimiento()
    {
        $this->verificarSesion();

        $codigo = trim($_GET['codigo'] ?? '');
        $id_cliente = $_SESSION['id_cliente'];
        $id_excluir = isset($_GET['id']) ? intval($_GET['id']) : null;

        if (empty($codigo)) {
            $this->responseJson(['existe' => false]);
            return;
        }

        $existe = Establecimiento::existeCodigoEstablecimiento($id_cliente, $codigo, $id_excluir);
        $this->responseJson(['existe' => $existe]);
    }

    private function actualizarEtiquetasVacias($id_cliente)
    {
        $cliente = Establecimiento::obtenerClientePorId($id_cliente);
        if (!$cliente) return;

        $conn = Conexion::conectar();
        $date_update = date('Y-m-d H:i:s');
        $user_update = SesionHelper::obtenerNombreUsuario();
        
        // Actualizar todos los establecimientos sin etiqueta
        $sql = "UPDATE establecimiento SET 
                etiqueta = ?, 
                user_update = ?, 
                date_update = ? 
                WHERE id_cliente = ? AND (etiqueta IS NULL OR etiqueta = '')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $cliente['razon_social'], $user_update, $date_update, $id_cliente);
        $stmt->execute();
    }
}
