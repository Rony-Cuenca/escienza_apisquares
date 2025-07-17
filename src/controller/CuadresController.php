<?php
ini_set('memory_limit', '512M');

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../model/Cuadre.php';
require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../model/Cliente.php';
require_once __DIR__ . '/../model/SerieAjena.php';
require_once __DIR__ . '/../model/SerieSucursal.php';
require_once __DIR__ . '/../model/DiferenciaComprobante.php';
require_once __DIR__ . '/../model/VentaGlobal.php';
require_once __DIR__ . '/../helpers/sesion_helper.php';
require_once __DIR__ . '/../model/Establecimiento.php';

class CuadresController
{
    public $validarNubox = [];
    public $validarSire = [];
    public $validarEDSuite = [];

    public $ResultsSIRE = [];
    public $ResultsEDSUITE = [];
    public $ResultsNUBOX = [];
    public $ResultsValidarSeries = [];
    public $resultsVentaGlobal = [];
    public $resultsSerieArchivos = [];
    public $diferenciaGlobales = [];

    public function index()
    {
        $sms = isset($_GET['sms']) ? $_GET['sms'] : null;
        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function cuadre()
    {
        if (isset($_FILES['exe_sire']) && $_FILES['exe_sire']['error'] === UPLOAD_ERR_OK) {
            $nombTemp = $_FILES['exe_sire']['tmp_name'];
            if (($handle = fopen($nombTemp, "r")) !== false) {
                $header = fgetcsv($handle);
                if ($header !== false && isset($header[0])) {
                    if (substr($header[0], 0, 3) === "\xEF\xBB\xBF") {
                        $header[0] = substr($header[0], 3);
                    }
                }
                $colRUC = array_search('Ruc', $header);
                if ($colRUC !== false) {
                    $dataRow = fgetcsv($handle);
                    if ($dataRow !== false && isset($dataRow[$colRUC])) {
                        $RUCSIRE = trim($dataRow[$colRUC]);
                    } else {
                        $ErrorSIRE = "No se pudo obtener el RUC del archivo SIRE.";
                    }
                } else {
                    $ErrorSIRE = "No se encontró la columna 'Ruc' en el archivo SIRE.";
                }
                fclose($handle);
            } else {
                $ErrorSIRE = "No se pudo abrir el archivo SIRE.";
            }
        } else {
            $ErrorSIRE = "No se subió ningún archivo válido.";
        }

        if (isset($_FILES['exe_nubox']) && $_FILES['exe_nubox']['error'] === UPLOAD_ERR_OK) {
            try {
                $nombTemp = $_FILES['exe_nubox']['tmp_name'];
                $reader = ReaderEntityFactory::createXLSXReader();
                $reader->open($nombTemp);
                $fila = 1;
                $RUCNUBOX = null;
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $row) {
                        if ($fila == 3) {
                            $cells = $row->toArray();
                            if (isset($cells[1]) && !empty($cells[1])) {
                                $RUCNUBOX = $cells[1];
                            } else {
                                $ErrorNUBOX = "No se pudo obtener el RUC del archivo NUBOX en la posición esperada.";
                            }
                            break 2; // ya no necesitamos seguir
                        }
                        $fila++;
                    }
                }
                $reader->close();

                if ($RUCNUBOX === null) {
                    $ErrorNUBOX = "No se pudo obtener el RUC del archivo NUBOX.";
                }
            } catch (Exception $e) {
                $ErrorNUBOX = "Error al procesar el archivo NUBOX: " . $e->getMessage();
            }
        } else {
            $ErrorNUBOX = "No se subió ningún archivo válido.";
        }

        if (!isset($_FILES['exe_edsuite']) || $_FILES['exe_edsuite']['error'] !== UPLOAD_ERR_OK) {
            $ErrorEDSUITE = "No se selecciono EDSUITE.";
        }

        // Verificar si hubo errores en el procesamiento de archivos
        if (isset($ErrorSIRE) || isset($ErrorNUBOX) || isset($ErrorEDSUITE)) {
            $errores = [];
            if (isset($ErrorSIRE)) $errores[] = "SIRE: " . $ErrorSIRE;
            if (isset($ErrorNUBOX)) $errores[] = "NUBOX: " . $ErrorNUBOX;
            if (isset($ErrorEDSUITE)) $errores[] = "EDSUITE: " . $ErrorEDSUITE;

            header("Location: index.php?controller=cuadres&error=" . urlencode(implode(". ", $errores)));
            exit();
        }

        // Usar el helper para obtener el usuario de manera unificada
        require_once __DIR__ . '/../helpers/sesion_helper.php';
        $userId = SesionHelper::obtenerUsuarioActual();

        if (!$userId) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("No se pudo determinar el usuario"));
            exit();
        }

        $user = Usuario::obtenerId($userId);

        // Validación simple: el usuario debe existir
        if (!$user) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("Usuario no encontrado"));
            exit();
        }

        // Obtener el cliente según el contexto (normal o superadmin directo)
        if (SesionHelper::esModoSuperAdmin()) {
            $id_cliente = SesionHelper::obtenerClienteActual();
        } else {
            $id_cliente = $user['id_cliente'];
        }

        $cliente = Cliente::obtenerCliente($id_cliente);
        $establecimientos = Establecimiento::obtenerEstablecimientoPorCliente($id_cliente);
        $rol = $user['rol'];

        // Validar que se haya obtenido el cliente
        if (!$cliente) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("Cliente no encontrado"));
            exit();
        }

        // Validar que se hayan obtenido los RUCs
        if (!isset($RUCSIRE) || empty($RUCSIRE)) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("No se pudo obtener el RUC del archivo SIRE"));
            exit();
        }

        if (!isset($RUCNUBOX) || empty($RUCNUBOX)) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("No se pudo obtener el RUC del archivo NUBOX"));
            exit();
        }

        if ($rol == 'Administrador') {
            $RUCCLiente = $RUCSIRE;
        } else {
            $RUCCLiente = $cliente['ruc'];
        }

        if ($RUCSIRE && $RUCNUBOX) {
            if ($RUCSIRE == $RUCNUBOX) {
                if ($RUCSIRE == $RUCCLiente) {
                    $SIRE = $this->sire();
                    extract($SIRE);
                    $this->ResultsSIRE = $SIRE['ResultsSIRE'];
                    $this->validarSire = $SIRE['ResultsSIRE'];
                    try {
                        $nuboxResponse = $this->cargar_archivo($_FILES['exe_nubox'], 1, 1);
                    } catch (Exception $e) {
                        $errorMsg = urlencode($e->getMessage());
                        header("Location: index.php?controller=cuadres&action=index&error={$errorMsg}");
                        exit;
                    }
                    if (isset($nuboxResponse['resultados']) && $nuboxResponse['estado'] == 1) {
                        $NUBOX = $this->procesarDatosNubox($nuboxResponse['resultados']);
                        extract($NUBOX);
                        $this->ResultsNUBOX = $NUBOX['ResultsNUBOX'];
                        $this->validarNubox = $NUBOX['ResultsNUBOX'];
                    }

                    $diferencia = $this->DiferenciaComprobante($SIRE['ResultsSIRE'], $NUBOX['ResultsNUBOX']);
                    if($diferencia != null){
                        $diferenciaGlobales = $this->VerificarDiferenciaComprobante($diferencia);

                        foreach ($diferenciaGlobales as &$diferenciaGlobale) {
                            if (isset($diferenciaGlobale['sire']['estado'])) {
                                $diferenciaGlobale['sire']['estado'] = ($diferenciaGlobale['sire']['estado'] == 1) ? 'Aceptado' : 'Anulado';
                            }
                        }
                        unset($diferenciaGlobale);
                        $this->diferenciaGlobales = $diferenciaGlobales;
                        $uploadDir = __DIR__ . '/../../uploads';
                        $this->limpiarCarpeta($uploadDir);
                    }else{
                        $uploadDir = __DIR__ . '/../../uploads';
                        $this->limpiarCarpeta($uploadDir);
                    }
                    
                    try {
                        $edsuiteResponse = $this->cargar_archivo($_FILES['exe_edsuite'], 2, 2);
                    } catch (Exception $e) {
                        $errorMsg = urlencode($e->getMessage());
                        header("Location: index.php?controller=cuadres&action=index&error={$errorMsg}");
                        exit;
                    }
                    if (isset($edsuiteResponse['resultados']) && $edsuiteResponse['estado'] == 2) {
                        $EDSUITE = $this->procesarDatosEDSuite($edsuiteResponse['resultados']);
                        extract($EDSUITE);

                        $this->ResultsEDSUITE = $EDSUITE['ResultsEDSUITE'];
                        $this->validarEDSuite = $EDSUITE['ResultsEDSUITE'];

                        $this->resultsVentaGlobal = $edsuiteResponse['resultados_productos'];
                        $this->resultsSerieArchivos = $edsuiteResponse['resultados_archivo'];

                        $uploadDir = __DIR__ . '/../../uploads';
                        $this->limpiarCarpeta($uploadDir);
                    } elseif (isset($edsuiteResponse['message'])) {
                        $ErrorEDSUITE = $edsuiteResponse['message'];
                        $uploadDir = __DIR__ . '/../../uploads';
                        $this->limpiarCarpeta($uploadDir);
                    }

                    $validarSeries = $this->Validar_series();
                    extract($validarSeries);
                    $this->ResultsValidarSeries = $validarSeries['ResultsValidarSeries'];
                } else {
                    $ErrorNUBOX = "Los RUC de los archivos no pertenece a la empresa.";
                }
            } else {
                $ErrorSIRE = "Los RUC de los archivos no coinciden.";
            }
        } else {
            $ErrorSIRE = "No se subieron archivos válidos.";
        }

        foreach ($establecimientos as &$establecimiento) {
            $series = SerieSucursal::obtenerSeriesPorEstablecimiento($establecimiento['id']);
            if ($series && isset($series['serie'])) {
                $establecimiento['serie'] = $series['serie'];
            } else {
                $establecimiento['serie'] = null;
            }
        }
        unset($establecimiento);

        $serieArchivos = $this->resultsSerieArchivos;
        foreach ($serieArchivos as $archivo) {
            $seriesString[] = implode('-', $archivo['series']);
        }
        unset($serieArchivos);

        $coincidentes = [];

        foreach ($establecimientos as $e) {
            if ($e['serie'] !== null && in_array($e['serie'], $seriesString)) {
                $coincidentes[] = $e['serie'];
            }
        }
        unset($establecimientos);

        $_SESSION['ResultsSIRE'] = $this->ResultsSIRE;
        $_SESSION['ResultsNUBOX'] = $this->ResultsNUBOX;
        $_SESSION['ResultsEDSUITE'] = $this->ResultsEDSUITE;
        $_SESSION['ResultsValidarSeries'] = $this->ResultsValidarSeries;
        $_SESSION['resultsVentaGlobal'] = $this->resultsVentaGlobal;
        $_SESSION['diferenciaGlobales'] = $this->diferenciaGlobales;
        if (empty($coincidentes)) {
            $_SESSION['resultsSerieArchivos'] = $this->resultsSerieArchivos;
        } else {
            $_SESSION['resultsSerieArchivos'] = null;
        }


        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function cargarBD()
    {
        if (!empty($_POST['resultsSerieArchivos'])) {
            $json = $_POST['resultsSerieArchivos'];
            $data = json_decode($json, true);

            if (is_array($data)) {
                $_SESSION['resultsSerieArchivos'] = $data;
            }
        }
        // Obtener información del usuario actual usando SesionHelper
        $userId = SesionHelper::obtenerUsuarioActual();
        $user = Usuario::obtenerId($userId);

        if (!$user) {
            throw new Exception("No se pudo obtener información del usuario");
        }

        $id_establecimiento = SesionHelper::obtenerEstablecimientoActual();
        $sire = $_SESSION['ResultsSIRE'][0]['fecha_registro'];
        $existeFecha = Cuadre::existeFecha($sire, $id_establecimiento);
        if (!$existeFecha) {
            $this->guardarBD(
                $_SESSION['ResultsSIRE'],
                $_SESSION['ResultsEDSUITE'],
                $_SESSION['ResultsNUBOX'],
                $_SESSION['ResultsValidarSeries'],
                $_SESSION['resultsVentaGlobal'],
                $_SESSION['resultsSerieArchivos'],
                $_SESSION['diferenciaGlobales']
            );
            header("Location: index.php?controller=cuadres&action=index&sms=1");
            exit();
        } else {
            header("Location: index.php?controller=cuadres&action=index&sms=2");
            exit();
        }
    }

    public function unirExcel()
    {
        try {
            if (!isset($_FILES['archivos_excel'])) {
                throw new Exception("No se recibieron archivos");
            }

            $archivos = $_FILES['archivos_excel'];

            // Preparar CURL para enviar archivos
            $curl = curl_init();
            $cfileArray = [];

            foreach ($archivos['tmp_name'] as $idx => $tmpPath) {
                $nombre = $archivos['name'][$idx];
                $cfile = new CURLFile(
                    $tmpPath,
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    $nombre
                );
                $cfileArray["archivos[$idx]"] = $cfile;
            }

            curl_setopt_array($curl, [
                CURLOPT_URL => "http://localhost:5000/unificar",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $cfileArray,
                CURLOPT_TIMEOUT => 30
            ]);


            $response = curl_exec($curl);

            if ($response === false) {
                $curlError = curl_error($curl);
                curl_close($curl);
                throw new Exception("Error de conexión con la API: $curlError");
            }
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            $respuesta = json_decode($response, true);

            if ($respuesta === null) {
                throw new Exception("La API devolvió una respuesta no válida: $response");
            }

            if (isset($respuesta['error'])) {
                throw new Exception("API: " . $respuesta['error']);
            }

            if ($httpCode !== 200 || !isset($respuesta['status']) || $respuesta['status'] !== 'success') {
                throw new Exception("Error inesperado de la API");
            }

            // Si todo está bien
            $archivo = $respuesta['archivo'];
            header("Location: index.php?controller=cuadres&modal=unificacionExitosa&archivo=" . urlencode($archivo));
            exit;
        } catch (Exception $e) {
            $errorMsg = urlencode($e->getMessage());
            header("Location: index.php?controller=cuadres&action=index&error={$errorMsg}");
            exit;
        }
    }

    public function sire()
    {
        $ErrorSIRE = null;
        $ResultsSIRE = [];
        $reporte = 2;

        $uploadDir = __DIR__ . '/../../uploads/sire/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'sire_' . date('Y-m-d_H-i-s') . '_' . basename($_FILES['exe_sire']['name']);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['exe_sire']['tmp_name'], $uploadPath)) {
            $DataSerieGraSIRE = [];
            $DataSerieExoSIRE = [];
            $DataSerieInaSIRE = [];
            $DataSerieIGVSIRE = [];
            $DataSerieDscBISIRE = [];
            $DataSerieDscIGVSIRE = [];
            if (($handle = fopen($uploadPath, "r")) != false) {
                $header = fgetcsv($handle);

                $colSerie = array_search('Serie del CDP', $header);
                $colGrav = array_search('BI Gravada', $header);
                $colExo = array_search('Mto Exonerado', $header);
                $colIna = array_search('Mto Inafecto', $header);
                $colIGV = array_search('IGV / IPM', $header);
                $colDscBI = array_search('Dscto BI', $header);
                $colDscIGV = array_search('Dscto IGV / IPM', $header);
                $colFecha = array_search('Fecha de emisión', $header);

                if (
                    $colSerie != false && $colGrav != false && $colExo != false &&
                    $colIna != false && $colIGV != false && $colDscBI != false && $colDscIGV != false && $colFecha != false
                ) {
                    $filas = [];
                    while (($fila = fgetcsv($handle)) !== false) {
                        $filas[] = $fila;
                    }
                    fclose($handle);

                    if (isset($filas[0][$colFecha])) {
                        $fechaSIRE = date('Y-d-01', strtotime($filas[0][$colFecha]));
                    }

                    foreach ($filas as $fila) {
                        $serieSIRE = $fila[$colSerie];
                        $GravSIRE = $fila[$colGrav];
                        $ExoSIRE = $fila[$colExo];
                        $InaSIRE = $fila[$colIna];
                        $IGVSIRE = $fila[$colIGV];
                        $DscBISIRE = $fila[$colDscBI];
                        $DscIGVSIRE = $fila[$colDscIGV];

                        if (!isset($DataSerieGraSIRE[$serieSIRE])) {
                            $DataSerieGraSIRE[$serieSIRE] = 0;
                            $DataSerieExoSIRE[$serieSIRE] = 0;
                            $DataSerieInaSIRE[$serieSIRE] = 0;
                            $DataSerieIGVSIRE[$serieSIRE] = 0;
                            $DataSerieDscBISIRE[$serieSIRE] = 0;
                            $DataSerieDscIGVSIRE[$serieSIRE] = 0;
                            $conteoSeriesSIRE[$serieSIRE] = 0;
                        }

                        $DataSerieGraSIRE[$serieSIRE] += floatval($GravSIRE);
                        $DataSerieExoSIRE[$serieSIRE] += floatval($ExoSIRE);
                        $DataSerieInaSIRE[$serieSIRE] += floatval($InaSIRE);
                        $DataSerieIGVSIRE[$serieSIRE] += floatval($IGVSIRE);
                        $DataSerieDscBISIRE[$serieSIRE] += floatval($DscBISIRE);
                        $DataSerieDscIGVSIRE[$serieSIRE] += floatval($DscIGVSIRE);
                        $conteoSeriesSIRE[$serieSIRE]++;
                    }

                    ksort($DataSerieGraSIRE);

                    foreach ($DataSerieGraSIRE as $serie => $totalBI) {
                        $TExoSIRE = $DataSerieExoSIRE[$serie];
                        $TInaSIRE = $DataSerieInaSIRE[$serie];
                        $TIGVSIRE = $DataSerieIGVSIRE[$serie];
                        $TDscBISIRE = $DataSerieDscBISIRE[$serie];
                        $TDscIGVSIRE = $DataSerieDscIGVSIRE[$serie];
                        $TTotalSIRE = $totalBI + $TExoSIRE + $TInaSIRE + $TIGVSIRE + $TDscBISIRE + $TDscIGVSIRE;

                        if ($TDscBISIRE == 0 && $TDscIGVSIRE == 0) {
                            $BI_Gravada = $totalBI;
                            $IGV = $TIGVSIRE;
                        } else {
                            $BI_Gravada = $TDscBISIRE;
                            $IGV = $TDscIGVSIRE;
                        }

                        if ($TTotalSIRE < 0) {
                            $tipo_comprobante = 3;
                        } else {
                            $letra = substr($serie, 0, 1);
                            if ($letra == 'B') {
                                $tipo_comprobante = 1;
                            } else {
                                $tipo_comprobante = 2;
                            }
                        }

                        $ResultsSIRE[] = [
                            'serie' => $serie,
                            'conteo' => $conteoSeriesSIRE[$serie],
                            'bi' => $BI_Gravada,
                            'exonerado' => $TExoSIRE,
                            'inafecto' => $TInaSIRE,
                            'igv' => $IGV,
                            'total' => $TTotalSIRE,
                            'tipo_comprobante' => $tipo_comprobante,
                            'reporte' => $reporte,
                            'fecha_registro' => $fechaSIRE
                        ];
                    }
                } else {
                    $ErrorSIRE = "No se encontraron las columnas necesarias en el archivo";
                }
            } else {
                $ErrorSIRE = "No se pudo abrir el archivo SIRE";
            }
        } else {
            $ErrorSIRE = "Error al subir el archivo SIRE";
        }

        return compact('ErrorSIRE', 'ResultsSIRE');
    }

    public function cargar_archivo($archivo, $estado, $reporte)
    {
        if ($reporte == 1) {
            $uploadDir = __DIR__ . '/../../uploads/nubox/';
        } else {
            $uploadDir = __DIR__ . '/../../uploads/edsuite/';
        }
        //Sirve para cargar la carpeta por si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if ($reporte == 1) {
            $fileName = 'nubox_' . date('Y-m-d_H-i-s') . '_' . basename($_FILES['exe_nubox']['name']);
            $uploadPath = $uploadDir . $fileName;
        } else {
            $fileName = 'edsuite_' . date('Y-m-d_H-i-s') . '_' . basename($_FILES['exe_edsuite']['name']);
            $uploadPath = $uploadDir . $fileName;
        }

        if (!move_uploaded_file($archivo['tmp_name'], $uploadPath)) {
            throw new Exception('No se pudo guardar el archivo en el servidor');
        }

        try {
            $respuesta = $this->llamarApiPython($uploadPath, $estado);

            return $respuesta;
        } finally {
            /*if (file_exists($archivoPath)) {
                unlink($archivoPath);
            }

            $this->limpiarCarpeta($uploadDir);*/
        }
    }

    private function llamarApiPython($archivoPath, $estado)
    {
        $url = 'http://localhost:5000/procesar?estado=' . $estado;

        $cfile = new CURLFile(
            $archivoPath,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['file' => $cfile],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception("Error al conectar con la API: $curlError");
        }
        $json = json_decode($response, true);

        if ($json === null) {
            throw new Exception("Respuesta de la API no es un JSON válido. Código HTTP: $httpCode. Respuesta: $response");
        }

        if (isset($json['status']) && $json['status'] === 'error') {
            throw new Exception("Error de la API: " . ($json['message'] ?? 'Error desconocido'));
        }
        if ($httpCode !== 200) {
            throw new Exception("Error HTTP $httpCode de la API con mensaje: " . ($json['message'] ?? 'Error desconocido'));
        }

        return $json;
    }

    private function procesarDatosNubox($datosNubox)
    {
        $ErrorNUBOX = null;
        $ResultsNUBOX = [];
        $reporte = 1;
        try {
            foreach ($datosNubox as $resultado) {
                $fecha = isset($resultado['fecha']) ? date('Y-m-01', strtotime($resultado['fecha'])) : date('Y-m-01');

                if ($resultado['total'] < 0) {
                    $tipo_comprobante = 3;
                } else {
                    $letra = substr($resultado['serie'], 0, 1);
                    if ($letra == 'B') {
                        $tipo_comprobante = 1;
                    } else {
                        $tipo_comprobante = 2;
                    }
                }

                $ResultsNUBOX[] = [
                    'serie' => $resultado['serie'],
                    'conteo' => $resultado['conteo'],
                    'bi' => $resultado['gravado'],
                    'exonerado' => $resultado['exonerado'],
                    'inafecto' => $resultado['inafecto'],
                    'igv' => $resultado['igv'],
                    'total' => $resultado['total'],
                    'tipo_comprobante' => $tipo_comprobante,
                    'reporte' => $reporte,
                    'fecha_registro' => $fecha
                ];
            }
        } catch (Exception $e) {
            throw $e;
        }

        return compact('ErrorNUBOX', 'ResultsNUBOX');
    }

    private function procesarDatosEDSuite($datosEDSuite)
    {
        $ErrorEDSUITE = null;
        $ResultsEDSUITE = [];
        $reporte = 3;

        // Verificar si hay datos recibidos
        if (empty($datosEDSuite)) {
            $ErrorEDSUITE = "No se recibieron datos para procesar";
            return compact('ErrorEDSUITE', 'ResultsEDSUITE');
        }

        foreach ($datosEDSuite as $resultado) {
            // Validar estructura de cada resultado
            if (!isset($resultado['serie'], $resultado['conteo'], $resultado['igv'], $resultado['total'])) {
                $ErrorEDSUITE = "Estructura de datos incorrecta";
                return compact('ErrorEDSUITE', 'ResultsEDSUITE');
            }
            $fecha = isset($resultado['fecha']) ? date('Y-d-01', strtotime($resultado['fecha'])) : date('Y-d-01');

            if ($resultado['total'] < 0) {
                $tipo_comprobante = 3;
            } else {
                $letra = substr($resultado['serie'], 0, 1);
                if ($letra == 'B') {
                    $tipo_comprobante = 1;
                } else {
                    $tipo_comprobante = 2;
                }
            }

            $ResultsEDSUITE[] = [
                'serie' => $resultado['serie'],
                'conteo' => $resultado['conteo'],
                'bi' => 0,
                'exonerado' => 0,
                'inafecto' => 0,
                'igv' => $resultado['igv'],
                'total' => $resultado['total'],
                'tipo_comprobante' => $tipo_comprobante,
                'reporte' => $reporte,
                'fecha_registro' => $fecha
            ];
        }
        return compact('ErrorEDSUITE', 'ResultsEDSUITE');
    }

    private function Validar_series()
    {
        $ErrorValidarSeries = null;
        $ResultsValidarSeries = [];

        $sire = $this->validarSire;
        $nubox = $this->validarNubox;
        $edsuite = $this->validarEDSuite;

        // Juntar todas las series con origen
        $all_series_data = [];

        foreach ($sire as $item) {
            $serie = $item['serie'];
            $all_series_data[$serie]['origenes']['SIRE'] = $item;
        }

        foreach ($nubox as $item) {
            $serie = $item['serie'];
            $all_series_data[$serie]['origenes']['NUBOX'] = $item;
        }

        foreach ($edsuite as $item) {
            $serie = $item['serie'];
            $all_series_data[$serie]['origenes']['EDSUITE'] = $item;
        }

        //Filtrar las series que NO estén en los 3
        foreach ($all_series_data as $serie => $data) {
            $origenes = $data['origenes'];
            if (count($origenes) < 3) {
                if (isset($origenes['NUBOX'])) {
                    
                    $item = $origenes['NUBOX'];
                    $ResultsValidarSeries[] = [
                        'serie'  => $item['serie'],
                        'conteo' => $item['conteo'],
                        'total'  => $item['total'],
                        'fecha'  => $item['fecha_registro'],
                        'cuadre' => 'NUBOX'
                    ];
                } else {
                    
                    foreach ($origenes as $origen => $item) {
                        $ResultsValidarSeries[] = [
                            'serie'  => $item['serie'],
                            'conteo' => $item['conteo'],
                            'total'  => $item['total'],
                            'fecha'  => $item['fecha_registro'],
                            'cuadre' => $origen
                        ];
                    }
                }
            }
        }

        $ResultsValidarSeriesDepurados = [];

        foreach ($ResultsValidarSeries as $registro) {
            if ($registro['total'] >= 0) {
                $ResultsValidarSeriesDepurados[] = $registro;
            }
        }

        $ResultsValidarSeries = $ResultsValidarSeriesDepurados;

        return compact('ErrorValidarSeries', 'ResultsValidarSeries');
    }

    private function generarCodigoAleatorio($longitud = 5)
    {
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $codigo = '';
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        return $codigo;
    }

    public function guardarBD($ResultsSIRE, $ResultsEDSUITE, $ResultsNUBOX, $ResultsValidarSeries, $resultsVentaGlobal, $resultsSerieArchivos, $diferenciaGlobales)
    {
        if (empty($ResultsSIRE) && empty($ResultsEDSUITE) && empty($ResultsNUBOX) && empty($ResultsValidarSeries) && empty($resultsVentaGlobal)) {
            throw new Exception("No se recibieron datos para guardar");
        }

        $user_create = SesionHelper::obtenerNombreUsuario();
        $user_update = SesionHelper::obtenerNombreUsuario();
        $id_establecimiento = SesionHelper::obtenerEstablecimientoActual();
        
        foreach ($ResultsSIRE as $resultado) {

            $establecimiento = null;

            // Verifica en el array de series por archivo
            if ($resultsSerieArchivos) {
                foreach ($resultsSerieArchivos as $archivoData) {
                    if (in_array($resultado['serie'], $archivoData['series'])) {
                        $establecimiento = $archivoData['id_establecimiento'];
                        break; // Salir al encontrar la primera coincidencia
                    }
                }
            }
        
            // Si no se encuentra el establecimiento, puedes manejarlo según tu lógica
            if (!$establecimiento) {
                $establecimiento = $id_establecimiento;
            }

            $data = [
                'serie' => $resultado['serie'],
                'cantidad_compr' => $resultado['conteo'],
                'suma_gravada' => $resultado['bi'],
                'suma_exonerada' => $resultado['exonerado'],
                'suma_inafecto' => $resultado['inafecto'],
                'suma_igv' => $resultado['igv'],
                'monto_total' => $resultado['total'],
                'tipo_comprobante' => $resultado['tipo_comprobante'],
                'id_reporte' => $resultado['reporte'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_establecimiento' => $establecimiento,
                'fecha_registro' => $resultado['fecha_registro'],
                'estado' => 1
            ];
            Cuadre::Insertar($data);
        }

        foreach ($ResultsEDSUITE as $resultado) {
            $establecimiento = null;

            // Verifica en el array de series por archivo
            if ($resultsSerieArchivos) {
                foreach ($resultsSerieArchivos as $archivoData) {
                    if (in_array($resultado['serie'], $archivoData['series'])) {
                        $establecimiento = $archivoData['id_establecimiento'];
                        break; // Salir al encontrar la primera coincidencia
                    }
                }
            }
        
            // Si no se encuentra el establecimiento, puedes manejarlo según tu lógica
            if (!$establecimiento) {
                $establecimiento = $id_establecimiento;
            }
            $data = [
                'serie' => $resultado['serie'],
                'cantidad_compr' => $resultado['conteo'],
                'suma_gravada' => $resultado['bi'],
                'suma_exonerada' => $resultado['exonerado'],
                'suma_inafecto' => $resultado['inafecto'],
                'suma_igv' => $resultado['igv'],
                'monto_total' => $resultado['total'],
                'tipo_comprobante' => $resultado['tipo_comprobante'],
                'id_reporte' => $resultado['reporte'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_establecimiento' => $establecimiento,
                'fecha_registro' => $resultado['fecha_registro'],
                'estado' => 1
            ];
            Cuadre::Insertar($data);
        }

        foreach ($ResultsNUBOX as $resultado) {
            $establecimiento = null;

            // Verifica en el array de series por archivo
            if ($resultsSerieArchivos) {
                foreach ($resultsSerieArchivos as $archivoData) {
                    if (in_array($resultado['serie'], $archivoData['series'])) {
                        $establecimiento = $archivoData['id_establecimiento'];
                        break; // Salir al encontrar la primera coincidencia
                    }
                }
            }
        
            // Si no se encuentra el establecimiento, puedes manejarlo según tu lógica
            if (!$establecimiento) {
                $establecimiento = $id_establecimiento;
            }
            $data = [
                'serie' => $resultado['serie'],
                'cantidad_compr' => $resultado['conteo'],
                'suma_gravada' => $resultado['bi'],
                'suma_exonerada' => $resultado['exonerado'],
                'suma_inafecto' => $resultado['inafecto'],
                'suma_igv' => $resultado['igv'],
                'monto_total' => $resultado['total'],
                'tipo_comprobante' => $resultado['tipo_comprobante'],
                'id_reporte' => $resultado['reporte'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_establecimiento' => $establecimiento,
                'fecha_registro' => $resultado['fecha_registro'],
                'estado' => 1
            ];
            Cuadre::Insertar($data);
        }

        foreach ($ResultsValidarSeries as $resultado) {
            $establecimiento = null;

            // Verifica en el array de series por archivo
            if ($resultsSerieArchivos) {
                foreach ($resultsSerieArchivos as $archivoData) {
                    if (in_array($resultado['serie'], $archivoData['series'])) {
                        $establecimiento = $archivoData['id_establecimiento'];
                        break; // Salir al encontrar la primera coincidencia
                    }
                }
            }
        
            // Si no se encuentra el establecimiento, puedes manejarlo según tu lógica
            if (!$establecimiento) {
                $establecimiento = $id_establecimiento;
            }
            $data = [
                'serie' => $resultado['serie'],
                'conteo' => $resultado['conteo'],
                'total' => $resultado['total'],
                'fecha_registro' => $resultado['fecha'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_establecimiento' => $establecimiento,
                'estado' => 1
            ];
            SerieAjena::Insertar($data);
        }

        foreach ($resultsVentaGlobal as $resultado) {
            $fecha = isset($resultado['fecha']) ? date('Y-d-01', strtotime($resultado['fecha'])) : date('Y-d-01');
            $data = [
                'producto' => $resultado['producto'],
                'cantidad' => $resultado['cantidad'],
                'total' => $resultado['total'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_establecimiento' => $id_establecimiento,
                'fecha_registro' => $fecha,
                'estado' => 1
            ];

            foreach ($data as $key => $value) {
                if ($value = false) {
                    throw new Exception("Dato inválido en campo $key");
                }
            }

            VentaGlobal::Insertar($data);
        }

        foreach ($resultsSerieArchivos as $resultado) {
            $id_establecimiento = $resultado['id_establecimiento'];

            $codigoAleatorio = $this->generarCodigoAleatorio(5);
            $codigo = $id_establecimiento . '-' . $codigoAleatorio;
            $seriesString = implode('-', $resultado['series']);

            $data = [
                'serie' => $seriesString,
                'codigo' => $codigo,
                'id_establecimiento' => $resultado['id_establecimiento'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'estado' => 1
            ];
            SerieSucursal::Insertar($data);
        }

        foreach ($diferenciaGlobales as $resultado) {
            $fecha = isset($resultado['nubox']['fecha']) ? date('Y-m-01', strtotime($resultado['nubox']['fecha'])) : date('Y-m-01');
            $data = [
                'serie' => $resultado['sire']['serie'],
                'numero' => $resultado['sire']['numero'],
                'total_sire' => $resultado['sire']['total'],
                'total_nubox' => $resultado['nubox']['total'],
                'estado_sire' => $resultado['sire']['estado'],
                'estado_nubox' => $resultado['nubox']['estado'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_establecimiento' => $id_establecimiento,
                'fecha_registro' => $fecha,
                'estado' => 1
            ];

            foreach ($data as $key => $value) {
                if ($value = false) {
                    throw new Exception("Dato inválido en campo $key");
                }
            }

            DiferenciaComprobante::Insertar($data);
        }
    }

    private function limpiarCarpeta($ruta)
    {
        if (!is_dir($ruta)) {
            return;
        }
    
        $items = scandir($ruta);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
    
            $path = $ruta . DIRECTORY_SEPARATOR . $item;
    
            if (is_dir($path)) {
                // Llamada recursiva para subcarpetas
                $this->limpiarCarpeta($path);
    
                // Borrar la carpeta vacía
                rmdir($path);
            } else {
                // Borrar archivo
                unlink($path);
            }
        }
    }
    

    private function DiferenciaComprobante($sire, $nubox)
    {
        $totalesNubox = [];
        foreach ($nubox as $item) {
            $totalesNubox[$item['serie']] = $item['total'];
        }

        $totalesSire = [];
        foreach ($sire as $item) {
            $totalesSire[$item['serie']] = $item['total'];
        }

        // Comparar por serie
        $diferencias = [];
        foreach ($totalesNubox as $serie => $totalNubox) {
            if (isset($totalesSire[$serie])) {
                $totalSire = $totalesSire[$serie];
                // Comparar total con tolerancia si deseas (ej. 0.01)
                if (abs($totalNubox - $totalSire) > 0.001) {
                    $diferencias[] = [
                        'serie' => $serie,
                        'total_nubox' => $totalNubox,
                        'total_sire' => $totalSire
                    ];
                }
            }
        }
        return $diferencias;
    }

    private function VerificarDiferenciaComprobante($diferencias)
    {
        $diferenciasEncontradas = [];

        foreach ($diferencias as $diferencia) {
            $data = [
                'serie' => $diferencia['serie'],
            ];

            $VerificacionSire = $this->VerificarSire($data);
            $VerificacionNubox = $this->VerificarNubox($data);

            $resultadosSire = $VerificacionSire;
            $resultadosNubox = $VerificacionNubox['resultados'] ?? [];

            // Indexar resultados de Nubox por "numero" para búsqueda rápida
            $nuboxIndexado = [];
            foreach ($resultadosNubox as $item) {
                $nuboxIndexado[$item['numero']] = $item;
            }

            // Comparar cada resultado de SIRE con el de NUBOX
            foreach ($resultadosSire as $sireItem) {
                $numero = $sireItem['numero'];
                if (isset($nuboxIndexado[$numero])) {
                    $nuboxItem = $nuboxIndexado[$numero];

                    if ($sireItem['total'] != $nuboxItem['total']) {
                        // Guardar la diferencia encontrada
                        $diferenciasEncontradas[] = [
                            'numero' => $numero,
                            'sire'   => $sireItem,
                            'nubox'  => $nuboxItem
                        ];
                    }
                }
            }
        }

        return $diferenciasEncontradas;
    }

    private function VerificarSire($data)
    {
        $uploadDir = __DIR__ . '/../../uploads/sire/';
        $series = $data['serie'];
        $registrosEncontrados = [];

        // Buscar todos los archivos SIRE en el directorio
        $archivosSIRE = glob($uploadDir . 'sire_*.csv');
        
        foreach ($archivosSIRE as $archivo) {
            if (($handle = fopen($archivo, "r")) !== false) {
                $header = fgetcsv($handle);
                
                $colSerie = array_search('Serie del CDP', $header);
                $colNumero = array_search('Nro CP o Doc. Nro Inicial (Rango)', $header);
                $colTotal = array_search('Total CP', $header);
                $colFecha = array_search('Fecha de emisión', $header);
                $colEstado = array_search('Est. Comp', $header);

                if ($colSerie !== false) {
                    while (($fila = fgetcsv($handle)) !== false) {
                        if ($fila[$colSerie] === $series) {
                            $registrosEncontrados[] = [
                                'serie' => $fila[$colSerie],
                                'numero' => $fila[$colNumero],
                                'total' => $fila[$colTotal],
                                'fecha' => $fila[$colFecha],
                                'estado' => $fila[$colEstado]
                            ];
                        }
                    }
                }
                fclose($handle);
            }
        }

        return $registrosEncontrados;
    }

    private function VerificarNubox($data)
    {
        $uploadDir = __DIR__ . '/../../uploads/nubox/';
        $seriesBuscada = $data['serie'];
    
        // Buscar todos los archivos Nubox en el directorio
        $archivosNUBOX = glob($uploadDir . 'nubox_*.xlsx');
    
        foreach ($archivosNUBOX as $archivo) {
            $url = 'http://localhost:5000/verificar?serie=' . $seriesBuscada;

            $cfile = new CURLFile(
                $archivo,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
    
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => ['file' => $cfile],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data'],
                CURLOPT_TIMEOUT => 30,
            ]);
    
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
    
            if ($response === false) {
                throw new Exception("Error al conectar con la API: $curlError");
            }
            $registrosEncontrados = json_decode($response, true);
    
            if ($registrosEncontrados === null) {
                throw new Exception("Respuesta de la API no es un JSON válido. Código HTTP: $httpCode. Respuesta: $response");
            }
    
            if (isset($registrosEncontrados['status']) && $registrosEncontrados['status'] === 'error') {
                throw new Exception("Error de la API: " . ($registrosEncontrados['message'] ?? 'Error desconocido'));
            }
            if ($httpCode !== 200) {
                throw new Exception("Error HTTP $httpCode de la API con mensaje: " . ($registrosEncontrados['message'] ?? 'Error desconocido'));
            }
        }
    
        return $registrosEncontrados;
    }
}
