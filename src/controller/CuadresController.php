<?php
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
require __DIR__ . '/../../vendor/autoload.php'; 
require_once __DIR__ . '/../model/Cuadre.php';
require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../model/SerieAjena.php';


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

        //$edsuiteResponse = $this->cargar_archivo($_FILES['exe_edsuite'], 2);
        //print_r($edsuiteResponse);
        /*if (isset($edsuiteResponse['resultados']) && $edsuiteResponse['estado'] == 2) {
            extract($this->procesarDatosEDSuite($edsuiteResponse['resultados']));
            //echo "Se cargo correctamente";
        } elseif (isset($edsuiteResponse['message'])) {
            $ErrorEDSUITE = $edsuiteResponse['message'];
        }*/

        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
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
        $conteoSeriesSIRE = [];
            
        if (($handle = fopen($nombTemp, "r")) != false) {
            $header = fgetcsv($handle);
            
            $colSerie = array_search('Serie del CDP', $header);
            $colnumero = array_search('Nro CP o Doc. Nro Inicial (Rango)', $header);
            $colGrav = array_search('BI Gravada', $header);
            $colExo = array_search('Mto Exonerado', $header);
            $colIna = array_search('Mto Inafecto', $header);
            $colIGV = array_search('IGV / IPM', $header);
            $colFecha = array_search('Fecha de emisión', $header);

            if ($colSerie != false && $colnumero != false && $colGrav != false && $colExo != false && 
                $colIna != false && $colIGV != false && $colFecha != false) {

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
                    $numeroSIRE = $fila[$colnumero];

                    $GravSIRE = $fila[$colGrav];
                    $ExoSIRE = $fila[$colExo];
                    $InaSIRE = $fila[$colIna];
                    $IGVSIRE = $fila[$colIGV];

                    $total_fila = $GravSIRE + $ExoSIRE + $InaSIRE + $IGVSIRE;
                    $this->validarSire[] = [
                        'serie' => $serieSIRE,
                        'total' => $total_fila
                    ];
    
                    if (!isset($DataSerieGraSIRE[$serieSIRE])) {
                        $DataSerieGraSIRE[$serieSIRE] = 0;
                        $DataSerieExoSIRE[$serieSIRE] = 0;
                        $DataSerieInaSIRE[$serieSIRE] = 0;
                        $DataSerieIGVSIRE[$serieSIRE] = 0;
                        $conteoSeriesSIRE[$serieSIRE] = 0;
                    }
    
                    $DataSerieGraSIRE[$serieSIRE] += floatval($GravSIRE);
                    $DataSerieExoSIRE[$serieSIRE] += floatval($ExoSIRE);
                    $DataSerieInaSIRE[$serieSIRE] += floatval($InaSIRE);
                    $DataSerieIGVSIRE[$serieSIRE] += floatval($IGVSIRE);
                    $conteoSeriesSIRE[$serieSIRE]++;
                }
                //print_r($this->validarSire);
    
                ksort($DataSerieGraSIRE);
                
                foreach ($DataSerieGraSIRE as $serie => $totalBI) {
                    $TExoSIRE = $DataSerieExoSIRE[$serie];
                    $TInaSIRE = $DataSerieInaSIRE[$serie];
                    $TIGVSIRE = $DataSerieIGVSIRE[$serie];
                    $TTotalSIRE = $totalBI + $TExoSIRE + $TInaSIRE + $TIGVSIRE;

                    $this->guardarCuadre($serie,$conteoSeriesSIRE[$serie],$totalBI,$TExoSIRE,$TInaSIRE,$TIGVSIRE,$TTotalSIRE,$reporte,$fechaSIRE);
                        
                    $ResultsSIRE[] = [
                        'serie' => $serie,
                        'conteo' => $conteoSeriesSIRE[$serie],
                        'bi' => $totalBI,
                        'exonerado' => $TExoSIRE,
                        'inafecto' => $TInaSIRE,
                        'igv' => $TIGVSIRE,
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
    
    public function guardarCuadre($serie,$conteo,$Gravada,$Exonerada,$Inafecto,$IGV,$Total,$reporte,$fecha_registro) {
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
                $this->guardarCuadre(
                    $resultado['serie'],
                    $resultado['conteo'],
                    $resultado['gravado'],
                    $resultado['exonerado'],
                    $resultado['inafecto'],
                    $resultado['igv'],
                    $resultado['total'],
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
            $fecha_original = "01-04-2025";
            $fecha_formateada = date('Y-m-01', strtotime($fecha_original));
            
            // Guardar en base de datos y capturar resultado
            $guardado = $this->guardarCuadre(
                $resultado['serie'],
                $resultado['conteo'],
                0,
                0,
                0,
                $resultado['igv'],
                $resultado['total'],
                $reporte,
                $fecha_formateada
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
    
        foreach ($faltantes_en_nubox as $serie) {
            $dato = null;
            foreach ($sire as $item) {
                if ($item['serie'] === $serie) {
                    $dato = $item;
                    break;
                }
            }

            $resultado[] = [
                'serie' => $serie,
                'total' => $dato['total'] ?? 0,
                'cuadre' => 'NUBOX'
            ];
        }
    
        foreach ($faltantes_en_sire as $serie) {
            $dato = null;
            foreach ($nubox as $item) {
                if ($item['serie'] === $serie) {
                    $dato = $item;
                    break;
                }
            }
    
            $resultado[] = [
                'serie' => $serie,
                'total' => $dato['total'] ?? 0,
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
                    'cuadre' => $cuadre
                ];
            }

            $agrupados[$serie]['conteo']++;
            $agrupados[$serie]['total'] += $total;

            $data = [
                'serie' => $serie,
                'conteo' => $agrupados[$serie]['conteo'],
                'total' => $agrupados[$serie]['total'],
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_sucursal' => $id_sucursal,
                'estado' => 1
            ];
    
            foreach ($data as $key => $value) {
                if ($value = false) {
                    throw new Exception("Dato inválido en campo $key");
                }
            }
    
            SerieAjena::Insertar($data);
        }

        // Si deseas que sea array con índices numéricos:
        $ResultsValidarSeries = array_values($agrupados);
    
        return compact('ErrorValidarSeries', 'ResultsValidarSeries');
    }

}
