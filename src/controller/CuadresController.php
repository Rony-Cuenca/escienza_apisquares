<?php
ini_set('memory_limit', '512M');
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
    
        if (isset($_FILES['exe_sire']) && $_FILES['exe_sire']['error'] == 0) {
            // Procesar archivo SIRE
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
        }
    
        if (isset($_FILES['exe_nubox']) && $_FILES['exe_nubox']['error'] == 0) {
            // Procesar archivo NUBOX (XLSX)
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
                            // La columna B es índice 1 (arrays empiezan en 0)
                            $RUCNUBOX = $cells[1];
                            break 2;
                        }
                        $fila++;
                    }
                }
                $reader->close();
                //throw new Exception($RUCNUBOX);
            } catch (Exception $e) {
                $ErrorNUBOX = $e->getMessage();
            }
        }

        if (!empty($RUCNUBOX) && !empty($RUCSIRE)) {
            if ($RUCSIRE == $RUCNUBOX) {
                extract($this->sire($_FILES['exe_sire'], $_GET['user']));
                extract($this->nubox($_FILES['exe_nubox'], $_GET['user']));
            } else {
                $ErrorSIRE = "Los RUC de los archivos no coinciden.";
            }
        } elseif (empty($RUCNUBOX)) {
            $ErrorNUBOX = "No se encontró el RUC en el archivo NUBOX.";
        } elseif (empty($RUCSIRE)) {
            $ErrorSIRE = "No se encontró el RUC en el archivo SIRE.";
        }

        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function sire() {
        $ErrorSIRE = null;
        $ResultsSIRE = [];
        $reporte = 2;

        try {
            // Inicializar variables para almacenar datos por serie
            $nombTemp = $_FILES['exe_sire']['tmp_name'];
            $DataSerieGraSIRE = [];
            $DataSerieExoSIRE = [];
            $DataSerieInaSIRE = [];
            $DataSerieIGVSIRE = [];
            $conteoSeriesSIRE = [];
                
            if (($handle = fopen($nombTemp, "r")) != false) {
                // Leer el encabezado del CSV
                $header = fgetcsv($handle);
                
                // Buscar las columnas necesarias en el CSV
                $colSerie = array_search('Serie del CDP', $header);
                $colGrav = array_search('BI Gravada', $header);
                $colExo = array_search('Mto Exonerado', $header);
                $colIna = array_search('Mto Inafecto', $header);
                $colIGV = array_search('IGV / IPM', $header);

                // Verificar si todas las columnas existen
                if ($colSerie != false && $colGrav != false && $colExo != false && 
                    $colIna != false && $colIGV != false) {
                    // Procesar cada fila del CSV
                    while (($datos = fgetcsv($handle, 0, ",")) !== false) {
                        $serieSIRE = $datos[$colSerie];
                        $GravSIRE = $datos[$colGrav];
                        $ExoSIRE = $datos[$colExo];
                        $InaSIRE = $datos[$colIna];
                        $IGVSIRE = $datos[$colIGV];

                        // Inicializar valores si es la primera vez que se ve la serie
                        if (!isset($DataSerieGraSIRE[$serieSIRE])) {
                            $DataSerieGraSIRE[$serieSIRE] = 0;
                            $DataSerieExoSIRE[$serieSIRE] = 0;
                            $DataSerieInaSIRE[$serieSIRE] = 0;
                            $DataSerieIGVSIRE[$serieSIRE] = 0;
                            $conteoSeriesSIRE[$serieSIRE] = 0;
                        }

                        // Acumular valores por serie
                        $DataSerieGraSIRE[$serieSIRE] += floatval($GravSIRE);
                        $DataSerieExoSIRE[$serieSIRE] += floatval($ExoSIRE);
                        $DataSerieInaSIRE[$serieSIRE] += floatval($InaSIRE);
                        $DataSerieIGVSIRE[$serieSIRE] += floatval($IGVSIRE);
                        $conteoSeriesSIRE[$serieSIRE]++;
                    }

                    // Ordenar las series
                    ksort($DataSerieGraSIRE);
                    
                    // Generar el array de resultados
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

        // Pasar los resultados a la vista
        return compact('ErrorSIRE', 'ResultsSIRE');
    }
    
    public function nubox() {
        $ErrorNUBOX = null;
        $ResultsNUBOX = [];
        $reporte = 1;
        
        try {
            $nombTemp = $_FILES['exe_nubox']['tmp_name'];
            $DataSerieGraNubox = [];
            $DataSerieExoNubox = [];
            $DataSerieInaNubox = [];
            $DataSerieIGVNubox = [];
            $conteoSeriesNubox = [];

            // Crear el lector
            $reader = ReaderEntityFactory::createXLSXReader();
            $reader->open($nombTemp);

            // Leer el encabezado (fila 8)
            $header = null;
            $filaActual = 1;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($filaActual == 4) { // Solo cuando llegamos a la fila 8
                        $cells = $row->toArray();
                        $header = $cells;
                        break;
                    }
                    $filaActual++;
                }
                if ($header !== null) break;
            }

            // Verificar si todas las columnas existen
            $colSerie = array_search('Número', $header);
            $colGravado = array_search('Total Gravado', $header);
            $colExonerado = array_search('Total Exonerado', $header);
            $colInafecto = array_search('Total Inafecto', $header);
            $colIGV = array_search('Total IGV', $header);

            if ($colSerie !== false && $colGravado !== false && $colExonerado !== false && 
                $colInafecto !== false && $colIGV !== false) {
                
                // Procesar cada fila (empezando desde la fila 9)
                foreach ($reader->getSheetIterator() as $sheet) {
                    $filaActual = 1;
                    foreach ($sheet->getRowIterator() as $row) {
                        if ($filaActual < 5) { // Saltar las filas 1-8
                            $filaActual++;
                            continue;
                        }
                        
                        $cells = $row->toArray();

                        if (
                            !isset($cells[$colSerie]) || trim($cells[$colSerie]) === '' ||
                            !isset($cells[$colGravado]) || trim($cells[$colGravado]) === '' ||
                            !isset($cells[$colExonerado]) || trim($cells[$colExonerado]) === '' ||
                            !isset($cells[$colInafecto]) || trim($cells[$colInafecto]) === '' ||
                            !isset($cells[$colIGV]) || trim($cells[$colIGV]) === ''
                        ) {
                            $filaActual++;
                            continue; // Saltar esta fila si hay campos faltantes o en blanco real
                        }
                        
                        // Extraer solo la parte antes del guion
                        $serie = explode('-', $cells[$colSerie])[0];
                        
                        $TGraNubox = floatval($cells[$colGravado]);
                        $TExoNubox = floatval($cells[$colExonerado]);
                        $TInaNubox = floatval($cells[$colInafecto]);
                        $TIGVNubox = floatval($cells[$colIGV]);

                        // Inicializar valores si es la primera vez que se ve la serie
                        if (!isset($DataSerieGraNubox[$serie])) {
                            $DataSerieGraNubox[$serie] = 0;
                            $DataSerieExoNubox[$serie] = 0;
                            $DataSerieInaNubox[$serie] = 0;
                            $DataSerieIGVNubox[$serie] = 0;
                            $conteoSeriesNubox[$serie] = 0;
                        }

                        // Acumular valores por serie
                        $DataSerieGraNubox[$serie] += $TGraNubox;
                        $DataSerieExoNubox[$serie] += $TExoNubox;
                        $DataSerieInaNubox[$serie] += $TInaNubox;
                        $DataSerieIGVNubox[$serie] += $TIGVNubox;
                        $conteoSeriesNubox[$serie]++;
                    }
                }

                // Ordenar las series
                ksort($DataSerieGraNubox);
                
                // Generar el array de resultados
                foreach ($DataSerieGraNubox as $serie => $totalGraNubox) {
                    $totalExoNubox = $DataSerieExoNubox[$serie];
                    $totalInaNubox = $DataSerieInaNubox[$serie];
                    $totalIGVNubox = $DataSerieIGVNubox[$serie];
                    $totalTotal = $totalGraNubox + $totalExoNubox + $totalInaNubox + $totalIGVNubox;

                    $this->guardarCuadre($serie, $conteoSeriesNubox[$serie], $totalGraNubox, 
                        $totalExoNubox, $totalInaNubox, $totalIGVNubox, $totalTotal, $reporte);
                        
                    $ResultsNUBOX[] = [
                        'serie' => $serie,
                        'conteo' => $conteoSeriesNubox[$serie],
                        'bi' => $totalGraNubox,
                        'exonerado' => $totalExoNubox,
                        'inafecto' => $totalInaNubox,
                        'igv' => $totalIGVNubox,
                        'total' => $totalTotal
                    ];
                }
            } else {
                $ErrorNUBOX = "No se encontraron las columnas necesarias en el archivo";
            }
            
            // Cerrar el lector
            $reader->close();
            
        } catch (Exception $e) {
            $ErrorNUBOX = "Error al procesar el archivo: " . $e->getMessage();
        }

        // Pasar los resultados a la vista
        return compact('ErrorNUBOX', 'ResultsNUBOX');
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
}

