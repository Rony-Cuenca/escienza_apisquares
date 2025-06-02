<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
require __DIR__ . '/../../vendor/autoload.php';


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
    
        if (isset($_FILES['exe_sire']) && $_FILES['exe_sire']['error'] == 0) {
            // Procesar archivo SIRE
            extract($this->sire($_FILES['exe_sire']));
        }
    
        if (isset($_FILES['exe_nubox']) && $_FILES['exe_nubox']['error'] == 0) {
            // Procesar archivo NUBOX
            extract($this->nubox($_FILES['exe_nubox']));
        }
    
        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function sire() {
        $ErrorSIRE = null;
        $ResultsSIRE = [];
        
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
        
        // Verificar si se subió el archivo XLSX de Nubox
        if (isset($_FILES['exe_nubox']) && $_FILES['exe_nubox']['error'] == 0) {
            try {
                // Inicializar variables para almacenar datos
                $nombTemp = $_FILES['exe_nubox']['tmp_name'];
                $datos = [];
                $filaInicio = 9;
                
                // Cargar el archivo XLSX
                $spreadsheet = IOFactory::load($nombTemp);
                $hoja = $spreadsheet->getActiveSheet();

                // Inicializar variables para almacenar datos por serie
                $DataSerieGraNubox = [];
                $DataSerieExoNubox = [];
                $DataSerieInaNubox = [];
                $DataSerieIGVNubox = [];
                $conteoSeriesNubox = [];

                while (true) {
                    $valorSerie = $hoja->getCell('D' . $filaInicio)->getValue();
                    
                    // Si ya no hay número, se asume fin de datos
                    if (empty($valorSerie)) break;

                    // Extraer solo la parte antes del guion
                    $serie = explode('-', $valorSerie)[0];

                    $TGraNubox = $hoja->getCell('AE' . $filaInicio)->getValue();
                    $TExoNubox = $hoja->getCell('AB' . $filaInicio)->getValue();
                    $TInaNubox = $hoja->getCell('AC' . $filaInicio)->getValue();
                    $TIGVNubox = $hoja->getCell('AG' . $filaInicio)->getValue();

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

                    $filaInicio++;
                }

                // Ordenar las series
                ksort($DataSerieGraNubox);
                
                // Generar el array de resultados
                foreach ($DataSerieGraNubox as $serie => $totalGraNubox) {
                    $totalExoNubox = $DataSerieExoNubox[$serie];
                    $totalInaNubox = $DataSerieInaNubox[$serie];
                    $totalIGVNubox = $DataSerieIGVNubox[$serie];
                    $totalTotal = $totalGraNubox + $totalExoNubox + $totalInaNubox + $totalIGVNubox;
                        
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
}

