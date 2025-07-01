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

    public function index() {
        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function cuadre() {
        $fechaSIRE = null;
        $fechaNUBOX = null;


        if (isset($_FILES['exe_sire']) && $_FILES['exe_sire']['error'] === UPLOAD_ERR_OK) {
            //Procesar RUC SIRE
            $nombTemp = $_FILES['exe_sire']['tmp_name'];
            if (($handle = fopen($nombTemp, "r")) != false) {
                $header = fgetcsv($handle);
                if ($header !== false && isset($header[0])) {
                    if (substr($header[0], 0, 3) == "\xEF\xBB\xBF") {
                        $header[0] = substr($header[0], 3);
                }
                }
                $colRUC = array_search('Ruc', $header);
                $colFecha = array_search('Fecha de emisión', $header);
                $dataRow = fgetcsv($handle);
                $RUCSIRE = trim($dataRow[$colRUC]);
                $fechaSIRE = trim($dataRow[$colFecha]);
                fclose($handle);
            } else {
                $ErrorSIRE = "No se pudo abrir el archivo SIRE.";
            }
            $fechaSIRE = date('Y-d-01', strtotime($fechaSIRE));
        } else {
            $ErrorSIRE = "No se subió ningún archivo válido.";

        }

        if (isset($_FILES['exe_nubox']) && $_FILES['exe_nubox']['error'] === UPLOAD_ERR_OK) {
            //Procesar RUC NUBOX
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
                    }
                    if ($fila == 5) {
                        $cells = $row->toArray();
                        $fechaNUBOX = $cells[4];
                    }
                    // Terminar el bucle si ya se capturaron ambos valores
                    if ($RUCNUBOX !== null && $fechaNUBOX !== null) {
                        break 2;
                    }
                    $fila++;
                }
            }
            $reader->close();
            $fechaNUBOX = date('Y-m-01', strtotime($fechaNUBOX));
        } else {
            $ErrorNUBOX = "No se subió ningún archivo válido.";
        }

        if ($RUCSIRE && $RUCNUBOX) {
            if ($RUCSIRE == $RUCNUBOX) {
                if ($fechaSIRE == $fechaNUBOX) {
                    $user = Usuario::obtenerId($_GET['user']);
                    $id_sucursal = $user['id_sucursal'];
                    $existeFecha = Cuadre::existeFecha($fechaSIRE, $id_sucursal);
                    if (!$existeFecha) {
                        extract($this->sire($_FILES['exe_sire'], $_GET['user']));
                        $nuboxResponse = $this->cargar_archivo($_FILES['exe_nubox'], 1);
                        if (isset($nuboxResponse['resultados']) && $nuboxResponse['estado'] == 1 && isset($nuboxResponse['validarNubox'])) {
                            extract($this->procesarDatosNubox($nuboxResponse['resultados']));
                            $this->validarNubox = $nuboxResponse['validarNubox'];
                        }
                        extract($this->Validar_series());
                        //print_r($this->validarNubox);
                    } else {
                        $ErrorSIRE = "Ya existe un cuadre para la fecha seleccionada.";
                    }
                } else {
                    $ErrorSIRE = "Las fechas de los archivos no coinciden.";
                }
            } else {
                $ErrorSIRE = "Los RUC de los archivos no coinciden.";
            }
        } else {
            $ErrorSIRE = "No se subieron archivos válidos.";
        }

        if (!isset($_FILES['exe_edsuite']) || $_FILES['exe_edsuite']['error'] !== UPLOAD_ERR_OK) {
            $ErrorEDSUITE = "No se selecciono EDSUITE.";
        } else {
            $edsuiteResponse = $this->cargar_archivo($_FILES['exe_edsuite'], 2);
            //print_r($edsuiteResponse['resultados_productos']);
            if (isset($edsuiteResponse['resultados']) && $edsuiteResponse['estado'] == 2) {
                extract($this->procesarDatosEDSuite($edsuiteResponse['resultados']));
                print_r($edsuiteResponse['resultados_productos']);
                $this->guardarDatosEdSuite($edsuiteResponse['resultados_productos']);
            } elseif (isset($edsuiteResponse['message'])) {
                $ErrorEDSUITE = $edsuiteResponse['message'];
            }
        }

        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function unirExcel() {
        if (!isset($_FILES['archivos_excel'])) {
            die("No se recibieron archivos");
        }
    
        $archivos = $_FILES['archivos_excel'];
    
        // Preparar CURL para enviar archivos
        $curl = curl_init();
        $cfileArray = [];
    
        foreach ($archivos['tmp_name'] as $idx => $tmpPath) {
            $nombre = $archivos['name'][$idx];
            $cfile = new CURLFile($tmpPath, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $nombre);
            $cfileArray["archivos[]"] = $cfile; // array de múltiples archivos
        }
    
        curl_setopt_array($curl, [
            CURLOPT_URL => "http://localhost:5000/unificar",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $cfileArray,
        ]);
    

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    
        $respuesta = json_decode($response, true);
    
        if ($httpCode === 200 && isset($respuesta['status']) && $respuesta['status'] === 'ok') {
            $archivo = $respuesta['archivo'];
            $url_descarga = "http://localhost:5000/descargas/" . urlencode($archivo);

            // Redirige con el archivo como parámetro (opcional: base64 o rawurlencode si es muy largo)
            header("Location: index.php?controller=cuadres&modal=unificacionExitosa&archivo=" . urlencode($archivo));
            exit;
        } else {
            $error = $respuesta['error'] ?? 'Error al unir los archivos.';
            echo "<script>alert('Error: {$error}'); window.history.back();</script>";
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

                    $this->guardarCuadre($serie,$conteoSeriesSIRE[$serie],$BI_Gravada,$TExoSIRE,$TInaSIRE,$IGV,$TTotalSIRE,$tipo_comprobante,$reporte,$fechaSIRE);
                        
                    $ResultsSIRE[] = [
                        'serie' => $serie,
                        'conteo' => $conteoSeriesSIRE[$serie],
                        'bi' => $BI_Gravada,
                        'exonerado' => $TExoSIRE,
                        'inafecto' => $TInaSIRE,
                        'igv' => $IGV,
                        'total' => $TTotalSIRE
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

        // Llamar al servicio Python con la ruta del archivo
        $respuesta = $this->llamarApiPython($archivoPath, $estado);

        // Opcional: Eliminar el archivo después de procesarlo
        unlink($archivoPath);

        return $respuesta;
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
            CURLOPT_VERBOSE => true  // Para debugging
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("API Python error ($httpCode)");
        }

        return json_decode($response, true);
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
                $this->guardarCuadre(
                    $resultado['serie'],
                    $resultado['conteo'],
                    $resultado['gravado'],
                    $resultado['exonerado'],
                    $resultado['inafecto'],
                    $resultado['igv'],
                    $resultado['total'],
                    $tipo_comprobante,
                    $reporte,
                    $fecha
                );
    
                $ResultsNUBOX[] = [
                    'serie' => $resultado['serie'],
                    'conteo' => $resultado['conteo'],
                    'gravado' => $resultado['gravado'],
                    'exonerado' => $resultado['exonerado'],
                    'inafecto' => $resultado['inafecto'],
                    'igv' => $resultado['igv'],
                    'total' => $resultado['total']
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

            // Guardar en base de datos y capturar resultado
            $guardado = $this->guardarCuadre(
                $resultado['serie'],
                $resultado['conteo'],
                0,
                0,
                0,
                $resultado['igv'],
                $resultado['total'],
                $tipo_comprobante,
                $reporte,
                $fecha
            );
            
            
            $ResultsEDSUITE[] = [
                'serie' => $resultado['serie'],
                'conteo' => $resultado['conteo'],
                'igv' => $resultado['igv'],
                'total' => $resultado['total']
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

        foreach ($agrupados as $item) {
            $data = [
                'serie' => $item['serie'],
                'conteo' => $item['conteo'],
                'total' => $item['total'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_sucursal' => $id_sucursal,
                'fecha_registro' => $item['fecha'],
                'estado' => 1
            ];
            SerieAjena::Insertar($data);
        }

        // Si deseas que sea array con índices numéricos:
        $ResultsValidarSeries = array_values($agrupados);
        //echo json_encode($ResultsValidarSeries);
    
        return compact('ErrorValidarSeries', 'ResultsValidarSeries');
    }

    public function guardarDatosEdSuite($resultados) {
        $user = Usuario::obtenerId($_GET['user']);
        
        $user_create = $user['usuario'];
        $user_update = $user['usuario'];
        $id_sucursal = $user['id_sucursal'];

        foreach ($resultados as $resultado) {
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
}
