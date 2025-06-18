<?php
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

        $ResultsEDSUITE = [];
        $ErrorEDSUITE = null;

        $RUCSIRE = null;
        $RUCNUBOX = null;

        // Verificar que se hayan subido ambos archivos
        if (!isset($_FILES['exe_sire']) || !isset($_FILES['exe_nubox'])) {
            throw new Exception('Debe subir ambos archivos (SIRE y Nubox360)');
        }

        // Process RUC SIRE
        $nombTemp = $_FILES['exe_sire']['tmp_name'];
        
        if (($handle = fopen($nombTemp, "r")) != false) {
            $header = fgetcsv($handle);
            if ($header !== false && isset($header[0])) {
                // Check for UTF-8 BOM (EF BB BF) and remove it
                if (substr($header[0], 0, 3) == "\xEF\xBB\xBF") {
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
        
        // Process RUC Nubox
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $nuboxPath = $uploadDir . uniqid('nubox_') . '.xlsx';
        move_uploaded_file($_FILES['exe_nubox']['tmp_name'], $nuboxPath);

        $nuboxResponseRUC = $this->carga_nubox(null, 2, $nuboxPath);
        $RUCNUBOX = $nuboxResponseRUC['resultados'] ?? null;

        if ($RUCSIRE == $RUCNUBOX) {
            // Procesar archivo SIRE
            extract($this->sire($_FILES['exe_sire'], $_GET['user']));

            // Estado 1: procesamiento real
            $nuboxResponseFull = $this->carga_nubox(null, 1, $nuboxPath);
            if (isset($nuboxResponseFull['resultados']) && $nuboxResponseFull['estado'] == 1) {
                extract($this->procesarDatosNubox($nuboxResponseFull['resultados']));
            }
        } else {
            $ErrorSIRE = "Los RUC de los archivos no coinciden.";
        }

        //$edsuiteResponse = $this->carga_nubox($_FILES['exe_edsuite'], 3);
        //$if (isset($edsuiteResponse['resultados']) && $edsuiteResponse['estado'] == 3) {
            //extract($this->procesarDatosEDSuite($edsuiteResponse['resultados']));
        //} elseif (isset($edsuiteResponse['message'])) {
            //$ErrorEDSUITE = $edsuiteResponse['message'];
        //}
        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function sire() {
        $ErrorSIRE = null;
        $ResultsSIRE = [];
        $reporte = 2;

        try {
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

                if ($colSerie != false && $colGrav != false && $colExo != false && 
                    $colIna != false && $colIGV != false) {
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

                        $this->guardarCuadre($serie,$conteoSeriesSIRE[$serie],$totalBI,$TExoSIRE,$TInaSIRE,$TIGVSIRE,$TTotalSIRE,$reporte);
                            
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
        } catch (Exception $e) {
            $ErrorSIRE = "Error al procesar el archivo: " . $e->getMessage();
        }

        return compact('ErrorSIRE', 'ResultsSIRE');
    }

    public function carga_nubox($archivo, $estado, $rutaDirecta = null) {
        $uploadDir = __DIR__ . '/../../uploads/';

        if ($rutaDirecta) {
            $nuboxPath = $rutaDirecta;
        } else {
            $nuboxPath = $uploadDir . uniqid('nubox_') . '.xlsx';
            move_uploaded_file($archivo['tmp_name'], $nuboxPath);
        }

        $respuesta = $this->llamarApiPython($nuboxPath, $estado);

        if (!$rutaDirecta) {
            unlink($nuboxPath); // solo si fue temporal
        }

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
    
    public function guardarCuadre($serie,$conteo,$Gravada,$Exonerada,$Inafecto,$IGV,$Total,$reporte) {
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
        foreach ($datosNubox as $resultado) {
            $this->guardarCuadre(
                $resultado['serie'],
                $resultado['conteo'],
                $resultado['gravado'],
                $resultado['exonerado'],
                $resultado['inafecto'],
                $resultado['igv'],
                $resultado['total'],
                $reporte
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
    
        return compact('ErrorNUBOX', 'ResultsNUBOX');
    }
    private function procesarDatosEDSuite($datosEDSuite) {
        $ErrorEDSuite = null;
        $ResultsEDSuite = [];
        $reporte = 3;
        foreach ($datosEDSuite as $resultado) {
            $this->guardarCuadre(
                $resultado['serie'],
                $resultado['conteo'],
                0,
                0,
                0,
                $resultado['igv'],
                $resultado['total'],
                $reporte
            );

            $ResultsEDSuite[] = [
                'serie' => $resultado['serie'],
                'conteo' => $resultado['conteo'],
                'gravado' => 0,
                'exonerado' => 0,
                'inafecto' => 0,
                'igv' => $resultado['igv'],
                'total' => $resultado['total']
            ];
        }
    
        return compact('ErrorEDSuite', 'ResultsEDSuite');
    }

}
