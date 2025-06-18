<?php
//ini_set('memory_limit', '512M');
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
require __DIR__ . '/../../vendor/autoload.php'; 
require_once __DIR__ . '/../model/Cuadre.php';
require_once __DIR__ . '/../model/Usuario.php';


class CuadresController {
    public function index() {
        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function cuadre() {
        $ResultsSIRE = [];
        $ErrorSIRE = null;
        $ResultsNUBOX = [];
        $ErrorNUBOX = null;

        $RUCSIRE = null;
        $RUCNUBOX = null;
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
                    //$fecha_obj = DateTime::createFromFormat('d-m-Y', $fechaNUBOX);
                    //$fechaNUBOX = $fecha_obj->format('Y/m/01');
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
                    $nuboxResponse = $this->carga_nubox($_FILES['exe_nubox'], 1);
                    if (isset($nuboxResponse['resultados']) && $nuboxResponse['estado'] == 1) {
                        extract($this->procesarDatosNubox($nuboxResponse['resultados']));
                    }
                } else {
                    $ErrorSIRE = "Ya existe un cuadre para la fecha seleccionada.";
                }
            } else {
                $ErrorSIRE = "Las fechas de los archivos no coinciden.";
            }
        } else {
            $ErrorSIRE = "Los RUC de los archivos no coinciden.";
        }

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
            $colGrav = array_search('BI Gravada', $header);
            $colExo = array_search('Mto Exonerado', $header);
            $colIna = array_search('Mto Inafecto', $header);
            $colIGV = array_search('IGV / IPM', $header);
            $colFecha = array_search('Fecha de emisión', $header);

            if ($colSerie != false && $colGrav != false && $colExo != false && 
                $colIna != false && $colIGV != false && $colFecha != false) {
                if (($fila = fgetcsv($handle)) !== false) {
                    $fechaSIRE = $fila[$colFecha];
                    $fechaSIRE = date('Y-d-01', strtotime($fechaSIRE));
                }
                while (($datos = fgetcsv($handle, 0, ",")) !== false) {
                    $serieSIRE = $datos[$colSerie];
                    $GravSIRE = $datos[$colGrav];
                    $ExoSIRE = $datos[$colExo];
                    $InaSIRE = $datos[$colIna];
                    $IGVSIRE = $datos[$colIGV];

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
            fclose($handle);
        } else {
            $ErrorSIRE = "Error al abrir el archivo.";
        }

        return compact('ErrorSIRE', 'ResultsSIRE');
    }

    public function carga_nubox($archivo,$estado) {
        $uploadDir = __DIR__ . '/../../uploads/';
        //Sirve para cargar la carpeta por si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $nuboxPath = $uploadDir . uniqid('nubox_') . '.xlsx';
        
        if (!move_uploaded_file($archivo['tmp_name'], $nuboxPath)) {
            throw new Exception('No se pudo guardar el archivo en el servidor');
        }

        // Llamar al servicio Python con la ruta del archivo
        $respuesta = $this->llamarApiPython($nuboxPath, $estado);

        // Opcional: Eliminar el archivo después de procesarlo
        unlink($nuboxPath);

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


}
