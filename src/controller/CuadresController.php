<?php
ini_set('memory_limit', '512M');
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '20M');
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
require __DIR__ . '/../../vendor/autoload.php'; 
require_once __DIR__ . '/../model/Cuadre.php';
require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../model/SerieAjena.php';
require_once __DIR__ . '/../model/VentaGlobal.php';


class CuadresController {
    public $validarNubox = [];
    public $validarSire = [];

    public $ResultsSIRE = [];
    public $ResultsEDSUITE = [];
    public $ResultsNUBOX = [];
    public $ResultsValidarSeries = [];
    public $resultsVentaGlobal = [];

    public function index() {
        $sms = isset($_GET['sms']) ? $_GET['sms'] : null;
        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function cuadre() {
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
                $dataRow = fgetcsv($handle);
                $RUCSIRE = trim($dataRow[$colRUC]);
                fclose($handle);
            } else {
                $ErrorSIRE = "No se pudo abrir el archivo SIRE.";
            }
        } else {
            $ErrorSIRE = "No se subió ningún archivo válido.";
        }

        if (isset($_FILES['exe_nubox']) && $_FILES['exe_nubox']['error'] === UPLOAD_ERR_OK) {
            $nombTemp = $_FILES['exe_nubox']['tmp_name'];
            $reader = ReaderEntityFactory::createXLSXReader();
            $reader->open($nombTemp);
            $fila = 1;
            $RUCNUBOX = null;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($fila == 3) {
                        $cells = $row->toArray();
                        $RUCNUBOX = $cells[1];
                        break 2; // ya no necesitamos seguir
                    }
                    $fila++;
                }
            }
            $reader->close();
        } else {
            $ErrorNUBOX = "No se subió ningún archivo válido.";
        }

        if (!isset($_FILES['exe_edsuite']) || $_FILES['exe_edsuite']['error'] !== UPLOAD_ERR_OK) {
            $ErrorEDSUITE = "No se selecciono EDSUITE.";
        }

        if ($RUCSIRE && $RUCNUBOX) {
            if ($RUCSIRE == $RUCNUBOX) {
                $SIRE = $this->sire($_FILES['exe_sire'], $_GET['user']);
                extract($SIRE);
                $this->ResultsSIRE = $SIRE['ResultsSIRE'];
                try {
                    $nuboxResponse = $this->cargar_archivo($_FILES['exe_nubox'], 1);
                } catch (Exception $e) {
                    $errorMsg = urlencode($e->getMessage());
                    header("Location: index.php?controller=cuadres&action=index&error={$errorMsg}");
                    exit;
                }
                if (isset($nuboxResponse['resultados']) && $nuboxResponse['estado'] == 1 && isset($nuboxResponse['validarNubox'])) {
                    $NUBOX = $this->procesarDatosNubox($nuboxResponse['resultados']);
                    extract($NUBOX);
                    $this->ResultsNUBOX = $NUBOX['ResultsNUBOX'];
                    $this->validarNubox = $nuboxResponse['validarNubox'];
                }
                $validarSeries = $this->Validar_series();
                extract($validarSeries);
                $this->ResultsValidarSeries = $validarSeries['ResultsValidarSeries'];

                try {
                    $edsuiteResponse = $this->cargar_archivo($_FILES['exe_edsuite'], 2);
                } catch (Exception $e) {
                    $errorMsg = urlencode($e->getMessage());
                    header("Location: index.php?controller=cuadres&action=index&error={$errorMsg}");
                    exit;
                }
                if (isset($edsuiteResponse['resultados']) && $edsuiteResponse['estado'] == 2) {
                    $EDSUITE = $this->procesarDatosEDSuite($edsuiteResponse['resultados']);
                    extract($EDSUITE);
                    
                    $this->ResultsEDSUITE = $EDSUITE['ResultsEDSUITE'];
    
                    $this->resultsVentaGlobal = $edsuiteResponse['resultados_productos'];
                } elseif (isset($edsuiteResponse['message'])) {
                    $ErrorEDSUITE = $edsuiteResponse['message'];
                }
            } else {
                $ErrorSIRE = "Los RUC de los archivos no coinciden.";
            }
        } else {
            $ErrorSIRE = "No se subieron archivos válidos.";
        }

        $_SESSION['ResultsSIRE'] = $this->ResultsSIRE;
        $_SESSION['ResultsNUBOX'] = $this->ResultsNUBOX;
        $_SESSION['ResultsEDSUITE'] = $this->ResultsEDSUITE;
        $_SESSION['ResultsValidarSeries'] = $this->ResultsValidarSeries;
        $_SESSION['resultsVentaGlobal'] = $this->resultsVentaGlobal;

        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function cargarBD() {
        $user = Usuario::obtenerId($_GET['user']);
        $id_sucursal = $user['id_sucursal'];
        $sire = $_SESSION['ResultsSIRE'][0]['fecha_registro'];
        $existeFecha = Cuadre::existeFecha($sire, $id_sucursal);
        if (!$existeFecha) {
            $this->guardarBD(
            $_SESSION['ResultsSIRE'],
            $_SESSION['ResultsEDSUITE'],
            $_SESSION['ResultsNUBOX'],
            $_SESSION['ResultsValidarSeries'],
            $_SESSION['resultsVentaGlobal']
            );
            header("Location: index.php?controller=cuadres&action=index&sms=1");
            exit();
        } else {
            header("Location: index.php?controller=cuadres&action=index&sms=2");
            exit();
        }

    }

    public function unirExcel() {
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

    public function sire() {
        $ErrorSIRE = null;
        $ResultsSIRE = [];
        $reporte = 2;

        $nombTemp = $_FILES['exe_sire']['tmp_name'];
        $DataSerieGraSIRE = [];
        $DataSerieExoSIRE = [];
        $DataSerieInaSIRE = [];
        $DataSerieIGVSIRE = [];
        $DataSerieDscBISIRE = [];
        $DataSerieDscIGVSIRE = [];
        $conteoSeriesSIRE = [];
            
        if (($handle = fopen($nombTemp, "r")) != false) {
            $header = fgetcsv($handle);
            
            $colSerie = array_search('Serie del CDP', $header);
            $colGrav = array_search('BI Gravada', $header);
            $colExo = array_search('Mto Exonerado', $header);
            $colIna = array_search('Mto Inafecto', $header);
            $colIGV = array_search('IGV / IPM', $header);
            $colDscBI = array_search('Dscto BI', $header);
            $colDscIGV = array_search('Dscto IGV / IPM', $header);
            $colFecha = array_search('Fecha de emisión', $header);

            if ($colSerie != false && $colGrav != false && $colExo != false && 
                $colIna != false && $colIGV != false && $colDscBI != false && $colDscIGV != false && $colFecha != false) {

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

                    $total_fila = floatval($GravSIRE) + floatval($ExoSIRE) + floatval($InaSIRE) + floatval($IGVSIRE) + floatval($DscBISIRE) + floatval($DscIGVSIRE);
                    $this->validarSire[] = [
                        'serie' => $serieSIRE,
                        'total' => $total_fila,
                        'fecha' => $fechaSIRE
                    ];
    
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
                //print_r($this->validarSire);
    
                ksort($DataSerieGraSIRE);
                
                foreach ($DataSerieGraSIRE as $serie => $totalBI) {
                    $TExoSIRE = $DataSerieExoSIRE[$serie];
                    $TInaSIRE = $DataSerieInaSIRE[$serie];
                    $TIGVSIRE = $DataSerieIGVSIRE[$serie];
                    $TDscBISIRE = $DataSerieDscBISIRE[$serie];
                    $TDscIGVSIRE = $DataSerieDscIGVSIRE[$serie];
                    $TTotalSIRE = $totalBI + $TExoSIRE + $TInaSIRE + $TIGVSIRE + $TDscBISIRE + $TDscIGVSIRE;

                    if ($TDscBISIRE == 0 && $TDscIGVSIRE == 0){
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
            $ErrorSIRE = "Error al abrir el archivo.";
        }

        return compact('ErrorSIRE', 'ResultsSIRE');
    }

    public function cargar_archivo($archivo,$estado) {
        $uploadDir = __DIR__ . '/../../uploads/';
        //Sirve para cargar la carpeta por si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $archivoPath = $uploadDir . uniqid('archivo_') . '.xlsx';
        
        if (!move_uploaded_file($archivo['tmp_name'], $archivoPath)) {
            throw new Exception('No se pudo guardar el archivo en el servidor');
        }

        try {
            $respuesta = $this->llamarApiPython($archivoPath, $estado);
    
            return $respuesta;
    
        } finally {
            if (file_exists($archivoPath)) {
                unlink($archivoPath);
            }
    
            $this->limpiarCarpeta($uploadDir);
        }
    }

    private function llamarApiPython($archivoPath, $estado) {
        $url = 'http://localhost:5000/procesar?estado='.$estado;

        $cfile = new CURLFile(
            $archivoPath, 
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
            'nubox.xlsx'
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
    
    public function guardarCuadre($serie,$conteo,$Gravada,$Exonerada,$Inafecto,$IGV,$Total,$tipo_comprobante,$reporte,$fecha_registro) {
        $user = Usuario::obtenerId($_GET['user']);
        
        $user_create = $user['usuario'];
        $user_update = $user['usuario'];
        $id_sucursal = $user['id_sucursal'];
        
        $data = [
            'serie' => $serie,
            'cantidad_compr' => $conteo,
            'suma_gravada' => $Gravada,
            'suma_exonerada' => $Exonerada,
            'suma_inafecto' => $Inafecto,
            'suma_igv' => $IGV,
            'monto_total' => $Total,
            'tipo_comprobante' => $tipo_comprobante,
            'id_reporte' => $reporte,
            'user_create' => $user_create,
            'user_update' => $user_update,
            'id_sucursal' => $id_sucursal,
            'fecha_registro' => $fecha_registro,
            'estado' => 1
        ];

        foreach ($data as $key => $value) {
            if ($value = false) {
                throw new Exception("Dato inválido en campo $key");
            }
        }

        Cuadre::Insertar($data);
    }

    private function procesarDatosNubox($datosNubox) {
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

    private function procesarDatosEDSuite($datosEDSuite) {
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

    private function Validar_series() {
        $user = Usuario::obtenerId($_GET['user']);
        
        $user_create = $user['usuario'];
        $user_update = $user['usuario'];
        $id_sucursal = $user['id_sucursal'];


        $ErrorValidarSeries = null;
        $ResultsValidarSeries = [];

        $sire = $this->validarSire;
        $nubox = $this->validarNubox;

        $numeros_sire = array_column($sire, 'serie');
        $numeros_nubox = array_column($nubox, 'serie');

        $faltantes_en_nubox = array_diff($numeros_sire, $numeros_nubox);
        $faltantes_en_sire = array_diff($numeros_nubox, $numeros_sire);

        $resultado = [];
        $totalSerie = 0;
        $fechas = [];
    
        foreach ($faltantes_en_nubox as $serie) {
            foreach ($sire as $item) {
                if ($item['serie'] === $serie) {
                    $totalSerie += $item['total'];
                    if (!empty($item['fecha'])) {
                        $fechas[] = $item['fecha'];
                    }
                }
            }

            $resultado[] = [
                'serie' => $serie,
                'total' => $totalSerie,
                'fecha' => !empty($fechas) ? min($fechas) : null,
                'cuadre' => 'NUBOX'
            ];
        }
    
        foreach ($faltantes_en_sire as $serie) {
            $totalSerie = 0;
            $fechas = [];
            foreach ($nubox as $item) {
                if ($item['serie'] === $serie) {
                    $totalSerie += $item['total'];
                    if (!empty($item['fecha'])) {
                        $fechas[] = $item['fecha'];
                    }
                }
            }
    
            $resultado[] = [
                'serie' => $serie,
                'total' => $totalSerie,
                'fecha' => !empty($fechas) ? min($fechas) : null,
                'cuadre' => 'SIRE'
            ];
        }

        $agrupados = [];

        foreach ($resultado as $item) {
            $serie = $item['serie'];
            $total = $item['total'];
            $cuadre = $item['cuadre'];

            if (!isset($agrupados[$serie])) {
                $agrupados[$serie] = [
                    'serie' => $serie,
                    'conteo' => 0,
                    'total' => 0,
                    'fecha' => $item['fecha'] ?? null,
                    'cuadre' => $cuadre
                ];
            }

            $agrupados[$serie]['conteo']++;
            $agrupados[$serie]['total'] += $total;
        }

        // Si deseas que sea array con índices numéricos:
        $ResultsValidarSeries = array_values($agrupados);
    
        return compact('ErrorValidarSeries', 'ResultsValidarSeries');
    }

    public function guardarBD($ResultsSIRE, $ResultsEDSUITE, $ResultsNUBOX, $ResultsValidarSeries, $resultsVentaGlobal) {
        if (empty($ResultsSIRE) && empty($ResultsEDSUITE) && empty($ResultsNUBOX) && empty($ResultsValidarSeries) && empty($resultsVentaGlobal)) {
            throw new Exception("No se recibieron datos para guardar");
        }

        $user = Usuario::obtenerId($_GET['user']);
        
        $user_create = $user['usuario'];
        $user_update = $user['usuario'];
        $id_sucursal = $user['id_sucursal'];

        foreach ($ResultsSIRE as $resultado) {
            $this->guardarCuadre(
                $resultado['serie'],
                $resultado['conteo'],
                $resultado['bi'],
                $resultado['exonerado'],
                $resultado['inafecto'],
                $resultado['igv'],
                $resultado['total'],
                $resultado['tipo_comprobante'],
                $resultado['reporte'],
                $resultado['fecha_registro']
            );
        }

        foreach ($ResultsEDSUITE as $resultado) {
            $this->guardarCuadre(
                $resultado['serie'],
                $resultado['conteo'],
                $resultado['bi'],
                $resultado['exonerado'],
                $resultado['inafecto'],
                $resultado['igv'],
                $resultado['total'],
                $resultado['tipo_comprobante'],
                $resultado['reporte'],
                $resultado['fecha_registro']
            );
        }

        foreach ($ResultsNUBOX as $resultado) {
            $this->guardarCuadre(
                $resultado['serie'],
                $resultado['conteo'],
                $resultado['bi'],
                $resultado['exonerado'],
                $resultado['inafecto'],
                $resultado['igv'],
                $resultado['total'],
                $resultado['tipo_comprobante'],
                $resultado['reporte'],
                $resultado['fecha_registro']
            );
        }

        foreach ($ResultsValidarSeries as $resultado) {
            $data = [
                'serie' => $resultado['serie'],
                'conteo' => $resultado['conteo'],
                'total' => $resultado['total'],
                'fecha_registro' => $resultado['fecha'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_sucursal' => $id_sucursal,
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
                'id_sucursal' => $id_sucursal,
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
    }

    private function limpiarCarpeta($ruta) {
        if (!is_dir($ruta)) {
            return;
        }
        $archivos = glob($ruta . '/*');
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                unlink($archivo);
            }
        }
    }
}
