<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
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
            // Procesar archivo NUBOX
            $nombTemp = $_FILES['exe_nubox']['tmp_name'];
            $spreadsheet = IOFactory::load($nombTemp);
            $hoja = $spreadsheet->getActiveSheet();
            $RUCNUBOX = $hoja->getCell('B4')->getValue();
        }

        if ($RUCSIRE == $RUCNUBOX) {
            extract($this->sire($_FILES['exe_sire'], $_GET['user']));
            extract($this->nubox($_FILES['exe_nubox'], $_GET['user']));
        }

        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function sire() {
        $ErrorSIRE = null;
        $ResultsSIRE = [];
        $reporte = 2;
        
        // Verificar si se subió el archivo CSV
        if (isset($_FILES['exe_sire']) && $_FILES['exe_sire']['error'] == 0) {
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
                            $DataSerieGraSIRE[$serieSIRE] += $GravSIRE;
                            $DataSerieExoSIRE[$serieSIRE] += $ExoSIRE;
                            $DataSerieInaSIRE[$serieSIRE] += $InaSIRE;
                            $DataSerieIGVSIRE[$serieSIRE] += $IGVSIRE;
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
        } else {
            $ErrorSIRE = "Error al cargar el archivo";
        }

        // Pasar los resultados a la vista
        return compact('ErrorSIRE', 'ResultsSIRE');
    }
    
    public function nubox() {
        $ErrorNUBOX = null;
        $ResultsNUBOX = [];
        $reporte = 1;
        
        // Verificar si se subió el archivo XLSX de Nubox
        if (isset($_FILES['exe_nubox']) && $_FILES['exe_nubox']['error'] == 0) {
            try {
                // Inicializar variables para almacenar datos
                $nombTemp = $_FILES['exe_nubox']['tmp_name'];
                
                // Cargar el archivo XLSX
                $spreadsheet = IOFactory::load($nombTemp);
                $hoja = $spreadsheet->getActiveSheet();

                // Obtener el encabezado (primera fila)
                $headerRow = 8;
                $header = [];
                $col = 1;
                while ($hoja->getCellByColumnAndRow($col, $headerRow)->getValue() !== null) {
                    $header[] = $hoja->getCellByColumnAndRow($col, $headerRow)->getValue();
                    $col++;
                }

                // Buscar las columnas necesarias
                $colSerie = array_search('Número', $header);
                $colGravado = array_search('Total Gravado', $header);
                $colExonerado = array_search('Total Exonerado', $header);
                $colInafecto = array_search('Total Inafecto', $header);
                $colIGV = array_search('Total IGV', $header);


                // Inicializar variables para almacenar datos por serie
                $DataSerieGraNubox = [];
                $DataSerieExoNubox = [];
                $DataSerieInaNubox = [];
                $DataSerieIGVNubox = [];
                $conteoSeriesNubox = [];
                
                // Empezar desde la fila 2 (después del encabezado)
                $FIni = 9;
                if ($colSerie != false && $colGravado != false && $colExonerado != false && 
                    $colInafecto != false && $colIGV != false) {
                                   
                    while (true) {
                        $valorSerie = $hoja->getCellByColumnAndRow($colSerie + 1, $FIni)->getValue();
                        // Si ya no hay número, se asume fin de datos
                        if (empty($valorSerie)) {
                            break;
                        }

                        // Extraer solo la parte antes del guion
                        $serie = explode('-', $valorSerie)[0];

                        $TGraNubox = $hoja->getCellByColumnAndRow($colGravado + 1, $FIni)->getValue();
                        $TExoNubox = $hoja->getCellByColumnAndRow($colExonerado + 1, $FIni)->getValue();
                        $TInaNubox = $hoja->getCellByColumnAndRow($colInafecto + 1, $FIni)->getValue();
                        $TIGVNubox = $hoja->getCellByColumnAndRow($colIGV + 1, $FIni)->getValue();

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

                        $FIni++;
                    }

                } else {
                    $ErrorNUBOX = "No se encontraron las columnas necesarias en el archivo";
                }    
                // Ordenar las series
                ksort($DataSerieGraNubox);
                
                // Generar el array de resultados
                foreach ($DataSerieGraNubox as $serie => $totalGraNubox) {
                    $totalExoNubox = $DataSerieExoNubox[$serie];
                    $totalInaNubox = $DataSerieInaNubox[$serie];
                    $totalIGVNubox = $DataSerieIGVNubox[$serie];
                    $totalTotal = $totalGraNubox + $totalExoNubox + $totalInaNubox + $totalIGVNubox;

                    $this->guardarCuadre($serie,$conteoSeriesNubox[$serie],$totalGraNubox,$totalExoNubox,$totalInaNubox,$totalIGVNubox,$totalTotal,$reporte);
                        
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
            } catch (Exception $e) {
                $ErrorNUBOX = "Error al procesar el archivo: " . $e->getMessage();
            }
        } else {
            $ErrorNUBOX = "Error al cargar el archivo";
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

