<?php
require_once 'model/Cliente.php';
require_once 'model/Establecimiento.php';
require_once 'config/api_config.php';

class ClienteController
{
    public function index()
    {
        $clientes = Cliente::obtenerTodos();
        $contenido = 'view/components/cliente.php';
        require 'view/layout.php';
    }

    public function consultarRuc()
    {
        header('Content-Type: application/json');

        $ruc = $_GET['ruc'] ?? '';

        if (empty($ruc) || strlen($ruc) !== 11 || !ctype_digit($ruc)) {
            echo json_encode([
                'success' => false,
                'error' => 'RUC debe tener 11 dígitos numéricos'
            ]);
            exit;
        }

        if (Cliente::existeRuc($ruc)) {
            echo json_encode([
                'success' => false,
                'error' => 'Este RUC ya está registrado en el sistema'
            ]);
            exit;
        }

        try {
            $apiResponse = $this->consultarApiFactiliza($ruc);

            if (!$apiResponse['success']) {
                echo json_encode($apiResponse);
                exit;
            }

            echo json_encode($apiResponse);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error interno al consultar RUC: ' . $e->getMessage()
            ]);
        }
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

        return [
            'success' => true,
            'data' => $infoBasica['data'],
            'tipo' => 'info_basica'
        ];
    }

    private function consultarRucInfo($ruc)
    {
        $url = ApiConfig::FACTILIZA_API_URL . '/ruc/info/' . $ruc;
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

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ruc = trim($_POST['ruc'] ?? '');
            $razon_social = trim($_POST['razon_social'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $departamento = trim($_POST['departamento'] ?? '');
            $provincia = trim($_POST['provincia'] ?? '');
            $distrito = trim($_POST['distrito'] ?? '');
            $establecimientos_data = $_POST['establecimientos_data'] ?? null;

            $errores = [];

            if (empty($ruc)) {
                $errores[] = 'El RUC es obligatorio';
            } elseif (!preg_match('/^\d{11}$/', $ruc)) {
                $errores[] = 'El RUC debe tener 11 dígitos';
            }

            if (empty($razon_social)) {
                $errores[] = 'La razón social es obligatoria';
            }

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido';
            }

            if (empty($errores) && Cliente::existeRuc($ruc)) {
                $errores[] = 'Ya existe un cliente con este RUC';
            }

            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                $_SESSION['form_data'] = $_POST;
                header('Location: ?controller=superadmin&action=clientes');
                exit;
            }

            try {
                $id_cliente = Cliente::crearCompleto($ruc, $razon_social, $email, $telefono, $direccion, $departamento, $provincia, $distrito);

                if ($id_cliente) {
                    $establecimiento_principal = [
                        'codigo_establecimiento' => '0000',
                        'tipo_establecimiento' => 'MATRIZ',
                        'etiqueta' => $razon_social,
                        'direccion' => $direccion ?: 'Sin dirección especificada',
                        'direccion_completa' => $direccion ?: 'Sin dirección especificada',
                        'departamento' => $departamento,
                        'provincia' => $provincia,
                        'distrito' => $distrito,
                        'estado' => 1,
                        'origen' => 'MANUAL'
                    ];

                    Establecimiento::crear($id_cliente, $establecimiento_principal);

                    $_SESSION['mensaje'] = "Cliente creado exitosamente con su establecimiento principal";
                    header('Location: ?controller=superadmin&action=clientes');
                } else {
                    $_SESSION['errores'] = ['Error al crear el cliente'];
                    header('Location: ?controller=superadmin&action=clientes');
                }
            } catch (Exception $e) {
                $_SESSION['errores'] = ['Error interno: ' . $e->getMessage()];
                header('Location: ?controller=superadmin&action=clientes');
            }
            exit;
        }
    }

    public function show($id)
    {
        $cliente = Cliente::obtenerPorId($id);
        if (!$cliente) {
            header('Location: ?controller=superadmin&action=clientes');
            exit;
        }
        $contenido = 'view/components/cliente_detalle.php';
        require 'view/layout.php';
    }

    public function editar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $ruc = trim($_POST['ruc'] ?? '');
            $razon_social = trim($_POST['razon_social'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $departamento = trim($_POST['departamento'] ?? '');
            $provincia = trim($_POST['provincia'] ?? '');
            $distrito = trim($_POST['distrito'] ?? '');

            $errores = [];

            // Validaciones básicas
            if ($id <= 0) {
                $errores[] = 'ID de cliente inválido';
            }

            if (empty($ruc)) {
                $errores[] = 'El RUC es obligatorio';
            } elseif (!preg_match('/^\d{11}$/', $ruc)) {
                $errores[] = 'El RUC debe tener 11 dígitos';
            }

            if (empty($razon_social)) {
                $errores[] = 'La razón social es obligatoria';
            }

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido';
            }

            // Verificar RUC duplicado (excluyendo el cliente actual)
            if (empty($errores) && Cliente::existeRucParaEdicion($ruc, $id)) {
                $errores[] = 'Ya existe otro cliente con este RUC';
            }

            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                $_SESSION['form_data'] = $_POST;
                header('Location: ?controller=superadmin&action=clientes');
                exit;
            }

            try {
                $resultado = Cliente::actualizar($id, $ruc, $razon_social, $email, $telefono, $direccion, $departamento, $provincia, $distrito);

                if ($resultado) {
                    $_SESSION['mensaje'] = "Cliente actualizado exitosamente";
                } else {
                    $_SESSION['errores'] = ['Error al actualizar el cliente'];
                }
            } catch (Exception $e) {
                $_SESSION['errores'] = ['Error interno: ' . $e->getMessage()];
            }

            header('Location: ?controller=superadmin&action=clientes');
            exit;
        }
    }

    public function cambiarEstado()
    {
        header('Content-Type: application/json');

        $id = intval($_GET['id'] ?? 0);
        $estado = intval($_GET['estado'] ?? 1);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            exit;
        }

        if (!in_array($estado, [1, 2])) { // 1=Activo, 2=Inactivo
            echo json_encode(['success' => false, 'error' => 'Estado inválido']);
            exit;
        }

        try {
            $resultado = Cliente::cambiarEstado($id, $estado);

            if ($resultado) {
                $estadoTexto = $estado == 1 ? 'activado' : 'desactivado';
                echo json_encode([
                    'success' => true,
                    'message' => "Cliente $estadoTexto exitosamente"
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo cambiar el estado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
        }
        exit;
    }

    public function obtenerDatos()
    {
        header('Content-Type: application/json');

        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            exit;
        }

        try {
            $cliente = Cliente::obtenerPorId($id);

            if ($cliente) {
                echo json_encode(['success' => true, 'data' => $cliente]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Cliente no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
        }
        exit;
    }
}
