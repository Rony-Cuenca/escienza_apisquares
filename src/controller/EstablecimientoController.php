<?php
require_once 'model/Establecimiento.php';
require_once 'config/conexion.php';
require_once 'config/api_config.php';

class EstablecimientoController
{
    public function index()
    {
        $this->verificarSesion();

        $id_cliente = $_SESSION['id_cliente'];
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;

        // Asegurar que existe al menos el establecimiento principal
        $this->asegurarEstablecimientoPrincipal($id_cliente);

        $establecimientos = Establecimiento::obtenerPorCliente($id_cliente, $limit, $offset);
        $total = Establecimiento::contarPorCliente($id_cliente);

        $contenido = 'view/components/establecimiento.php';
        require 'view/layout.php';
    }
    
    private function asegurarEstablecimientoPrincipal($id_cliente)
    {
        // Verificar si existe al menos un establecimiento activo
        $conn = Conexion::conectar();
        $sql = "SELECT COUNT(*) as total FROM establecimiento WHERE id_cliente = ? AND estado IN (1, 2)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'];
        
        if ($count == 0) {
            // Crear establecimiento principal
            $cliente = Establecimiento::obtenerClientePorId($id_cliente);
            if ($cliente) {
                $datos = [
                    'id_cliente' => $id_cliente,
                    'codigo_establecimiento' => '0000',
                    'tipo_establecimiento' => 'MATRIZ',
                    'direccion' => $cliente['direccion'] ?? 'Sin dirección',
                    'direccion_completa' => $cliente['direccion'] ?? 'Sin dirección',
                    'departamento' => $cliente['departamento'] ?? '',
                    'provincia' => $cliente['provincia'] ?? '',
                    'distrito' => $cliente['distrito'] ?? '',
                    'user_create' => $_SESSION['usuario'] ?? 'sistema',
                    'user_update' => $_SESSION['usuario'] ?? 'sistema',
                    'estado' => 1
                ];
                
                Establecimiento::insertar($datos);
            }
        }
    }    public function crear()
    {
        // Esta función ya no se usará para crear establecimientos manualmente
        // Los establecimientos se crean/actualizan solo mediante sincronización
        header('Location: index.php?controller=establecimiento');
        exit;
    }

    public function editar()
    {
        // Esta función ya no se usará para editar datos de establecimientos
        // Solo se permite cambiar estados mediante cambiarEstado()
        header('Location: index.php?controller=establecimiento');
        exit;
    }

    public function sincronizarEstablecimientos()
    {
        $this->verificarSesion();
        
        $id_cliente = $_SESSION['id_cliente'];
        
        // Obtener datos del cliente
        $cliente = Establecimiento::obtenerClientePorId($id_cliente);
        
        if (!$cliente) {
            echo json_encode([
                'success' => false,
                'error' => 'Cliente no encontrado'
            ]);
            exit;
        }
        
        // Consultar API de SUNAT
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
        $debug_info = [];
        
        // DEBUG: Agregar información de la respuesta de la API
        $debug_info['tipo_respuesta'] = $apiResponse['tipo'];
        $debug_info['tiene_establecimientos'] = isset($apiResponse['establecimientos']);
        if (isset($apiResponse['establecimientos'])) {
            $debug_info['cantidad_establecimientos'] = count($apiResponse['establecimientos']);
            $debug_info['codigos_encontrados'] = array_map(function($est) {
                return $est['codigo_establecimiento'] ?? 'sin_codigo';
            }, $apiResponse['establecimientos']);
        }
        
        // Procesar establecimientos según el tipo de respuesta
        if ($apiResponse['tipo'] === 'con_establecimientos' && isset($apiResponse['establecimientos'])) {
            // Tiene múltiples establecimientos
            foreach ($apiResponse['establecimientos'] as $est) {
                $resultado = $this->procesarEstablecimiento($est, $id_cliente);
                $debug_info['establecimientos_procesados'][] = [
                    'codigo' => $resultado['codigo'],
                    'accion' => $resultado['accion']
                ];
                if ($resultado['accion'] === 'creado') {
                    $establecimientosCreados++;
                } elseif ($resultado['accion'] === 'actualizado') {
                    $establecimientosActualizados++;
                }
            }
        } else if ($apiResponse['tipo'] === 'info_basica' && isset($apiResponse['data'])) {
            // Solo establecimiento principal
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
            'actualizados' => $establecimientosActualizados,
            'debug' => $debug_info
        ]);
        exit;
    }
    
    private function procesarEstablecimiento($datosEstablecimiento, $id_cliente)
    {
        // Mapear correctamente los campos del API de Factiliza
        $codigo = $datosEstablecimiento['codigo_establecimiento'] ?? 
                  $datosEstablecimiento['codigo'] ?? '0000';
        $tipo = $datosEstablecimiento['tipo_establecimiento'] ?? 'MATRIZ';
        $direccion = $datosEstablecimiento['direccion'] ?? '';
        $direccion_completa = $datosEstablecimiento['direccion_completa'] ?? $direccion;
        $departamento = $datosEstablecimiento['departamento'] ?? '';
        $provincia = $datosEstablecimiento['provincia'] ?? '';
        $distrito = $datosEstablecimiento['distrito'] ?? '';
        
        // Verificar si ya existe el establecimiento por código
        $establecimientoExistente = Establecimiento::obtenerPorCodigoYCliente($codigo, $id_cliente);
        
        $datosCompletos = [
            'id_cliente' => $id_cliente,
            'codigo_establecimiento' => $codigo,
            'tipo_establecimiento' => $tipo,
            'direccion' => $direccion,
            'direccion_completa' => $direccion_completa,
            'departamento' => $departamento,
            'provincia' => $provincia,
            'distrito' => $distrito,
            'user_create' => $_SESSION['usuario'],
            'user_update' => $_SESSION['usuario'],
            'estado' => 1 // Por defecto activo
        ];
        
        if ($establecimientoExistente) {
            // Actualizar establecimiento existente
            Establecimiento::actualizarPorCodigo($establecimientoExistente['id'], $datosCompletos);
            return ['accion' => 'actualizado', 'codigo' => $codigo];
        } else {
            // Crear nuevo establecimiento
            Establecimiento::insertar($datosCompletos);
            return ['accion' => 'creado', 'codigo' => $codigo];
        }
    }

    public function cambiarEstado($id, $estado)
    {
        $this->verificarSesion();

        $id = intval($id);
        $estado = intval($estado);
        $id_cliente = $_SESSION['id_cliente'];

        if ($id <= 0 || !in_array($estado, [1, 2, 3])) {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
            return;
        }

        $resultado = Establecimiento::cambiarEstado($id, $estado, $id_cliente);

        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo cambiar el estado']);
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

        // Verificar si existe en base de datos
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $existe = Establecimiento::existeRuc($ruc, $id);

        // Consultar API de Factiliza para obtener establecimientos
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

        // Retornar información del RUC
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

        // Consultar API para obtener establecimientos
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
            // Formatear establecimientos para el select
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
            // Solo tiene establecimiento principal (matriz)
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
        // Verificar configuración del token
        if (empty(ApiConfig::FACTILIZA_TOKEN)) {
            return [
                'success' => false,
                'error' => 'Token de API no configurado. Contacte al administrador.'
            ];
        }

        // Primero consultar información básica del RUC
        $infoBasica = $this->consultarRucInfo($ruc);

        if (!$infoBasica['success']) {
            return $infoBasica;
        }

        // Luego consultar establecimientos/anexos
        $establecimientos = $this->consultarRucEstablecimientos($ruc);

        // Si tiene establecimientos, retornar ambos
        if ($establecimientos['success'] && !empty($establecimientos['data'])) {
            return [
                'success' => true,
                'info_basica' => $infoBasica['data'],
                'establecimientos' => $establecimientos['data'],
                'tipo' => 'con_establecimientos'
            ];
        } else {
            // Solo información básica
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
            // Configuraciones SSL para resolver el error de certificado
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
        // Nota: La dirección se valida desde el select del establecimiento, no aquí
        return false;
    }

    private function verificarSesion()
    {
        if (!isset($_SESSION['id_cliente'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }
}
