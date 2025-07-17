<?php


require_once __DIR__ . '/ArchivoService.php';
require_once __DIR__ . '/ApiPythonService.php';

class CuadreService
{
    private $archivoService;
    private $apiPythonService;

    public function __construct()
    {
        $this->archivoService = new ArchivoService();
        $this->apiPythonService = new ApiPythonService();
    }
    public function procesarSire($uploadPath, $fileName)
    {
        $ErrorSIRE = null;
        $ResultsSIRE = [];
        $reporte = 2;
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

        return compact('ErrorSIRE', 'ResultsSIRE');
    }

    public function generarCodigoAleatorio($longitud = 5)
    {
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $codigo = '';
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        return $codigo;
    }

    public function diferenciaComprobante($sire, $nubox)
    {
        $totalesNubox = [];
        foreach ($nubox as $item) {
            $totalesNubox[$item['serie']] = $item['total'];
        }

        $totalesSire = [];
        foreach ($sire as $item) {
            $totalesSire[$item['serie']] = $item['total'];
        }

        $diferencias = [];
        foreach ($totalesNubox as $serie => $totalNubox) {
            if (isset($totalesSire[$serie])) {
                $totalSire = $totalesSire[$serie];
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

    public function validarRucSire($filePath)
    {
        if (($handle = fopen($filePath, "r")) !== false) {
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
                    $ruc = trim($dataRow[$colRUC]);
                } else {
                    fclose($handle);
                    throw new Exception("No se pudo obtener el RUC del archivo SIRE.");
                }
            } else {
                fclose($handle);
                throw new Exception("No se encontró la columna 'Ruc' en el archivo SIRE.");
            }
            fclose($handle);
            return $ruc;
        } else {
            throw new Exception("No se pudo abrir el archivo SIRE.");
        }
    }

    public function validarRucNubox($filePath)
    {
        $reader = \Box\Spout\Reader\Common\Creator\ReaderEntityFactory::createXLSXReader();
        $reader->open($filePath);
        $fila = 1;
        $RUCNUBOX = null;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                if ($fila == 3) {
                    $cells = $row->toArray();
                    if (isset($cells[1]) && !empty($cells[1])) {
                        $RUCNUBOX = $cells[1];
                    } else {
                        $reader->close();
                        throw new Exception("No se pudo obtener el RUC del archivo NUBOX en la posición esperada.");
                    }
                    break 2;
                }
                $fila++;
            }
        }
        $reader->close();
        if ($RUCNUBOX === null) {
            throw new Exception("No se pudo obtener el RUC del archivo NUBOX.");
        }
        return $RUCNUBOX;
    }

    // Procesa los datos de Nubox y retorna resultados estructurados
    public function procesarDatosNubox($datosNubox)
    {
        $ErrorNUBOX = null;
        $ResultsNUBOX = [];
        $reporte = 1;
        try {
            foreach ($datosNubox as $resultado) {
                $fecha = isset($resultado['fecha']) ? date('Y-m-01', strtotime($resultado['fecha'])) : date('Y-m-01');
                $tipo_comprobante = ($resultado['total'] < 0) ? 3 : (substr($resultado['serie'], 0, 1) == 'B' ? 1 : 2);
                // Mapear 'gravado' a 'bi' para compatibilidad
                $bi = isset($resultado['bi']) ? $resultado['bi'] : (isset($resultado['gravado']) ? $resultado['gravado'] : 0);
                $ResultsNUBOX[] = [
                    'serie' => $resultado['serie'],
                    'conteo' => $resultado['conteo'],
                    'bi' => $bi,
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

    // Procesa los datos de EDSuite y retorna resultados estructurados
    public function procesarDatosEDSuite($datosEDSuite)
    {
        $ErrorEDSUITE = null;
        $ResultsEDSUITE = [];
        $reporte = 3;
        if (empty($datosEDSuite)) {
            $ErrorEDSUITE = "No se recibieron datos para procesar";
            return compact('ErrorEDSUITE', 'ResultsEDSUITE');
        }
        foreach ($datosEDSuite as $resultado) {
            if (!isset($resultado['serie'], $resultado['conteo'], $resultado['igv'], $resultado['total'])) {
                $ErrorEDSUITE = "Estructura de datos incorrecta";
                return compact('ErrorEDSUITE', 'ResultsEDSUITE');
            }
            $fecha = isset($resultado['fecha']) ? date('Y-m-01', strtotime($resultado['fecha'])) : date('Y-m-01');
            $tipo_comprobante = ($resultado['total'] < 0) ? 3 : (substr($resultado['serie'], 0, 1) == 'B' ? 1 : 2);
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

    // Valida y depura series entre SIRE, NUBOX y EDSUITE
    public function validarSeries($sire, $nubox, $edsuite)
    {
        $ErrorValidarSeries = null;
        $ResultsValidarSeries = [];
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
        foreach ($all_series_data as $serie => $data) {
            $origenes = $data['origenes'];
            if (count($origenes) < 3) {
                if (isset($origenes['NUBOX'])) {
                    $registro = $origenes['NUBOX'];
                    $registro['cuadre'] = 'NUBOX';
                    $ResultsValidarSeries[] = $registro;
                } elseif (isset($origenes['SIRE'])) {
                    $registro = $origenes['SIRE'];
                    $registro['cuadre'] = 'SIRE';
                    $ResultsValidarSeries[] = $registro;
                } elseif (isset($origenes['EDSUITE'])) {
                    $registro = $origenes['EDSUITE'];
                    $registro['cuadre'] = 'EDSUITE';
                    $ResultsValidarSeries[] = $registro;
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

    // (Método guardarCuadre eliminado, la lógica se integra en guardarBD)

    // Guarda todos los resultados en la base de datos
    public function guardarBD($ResultsSIRE, $ResultsEDSUITE, $ResultsNUBOX, $ResultsValidarSeries, $resultsVentaGlobal, $resultsSerieArchivos, $diferenciaGlobales)
    {
        if (empty($ResultsSIRE) && empty($ResultsEDSUITE) && empty($ResultsNUBOX) && empty($ResultsValidarSeries) && empty($resultsVentaGlobal)) {
            throw new Exception("No se recibieron datos para guardar");
        }
        $user_create = SesionHelper::obtenerNombreUsuario();
        $user_update = SesionHelper::obtenerNombreUsuario();
        $id_establecimiento = SesionHelper::obtenerEstablecimientoActual();

        // Guardar SIRE
        foreach ($ResultsSIRE as $resultado) {
            $establecimiento = null;
            if ($resultsSerieArchivos) {
                foreach ($resultsSerieArchivos as $archivoData) {
                    if (in_array($resultado['serie'], $archivoData['series'])) {
                        $establecimiento = $archivoData['id_establecimiento'];
                        break;
                    }
                }
            }
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

        // Guardar EDSUITE
        foreach ($ResultsEDSUITE as $resultado) {
            $establecimiento = null;
            if ($resultsSerieArchivos) {
                foreach ($resultsSerieArchivos as $archivoData) {
                    if (in_array($resultado['serie'], $archivoData['series'])) {
                        $establecimiento = $archivoData['id_establecimiento'];
                        break;
                    }
                }
            }
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

        // Guardar NUBOX
        foreach ($ResultsNUBOX as $resultado) {
            $establecimiento = null;
            if ($resultsSerieArchivos) {
                foreach ($resultsSerieArchivos as $archivoData) {
                    if (in_array($resultado['serie'], $archivoData['series'])) {
                        $establecimiento = $archivoData['id_establecimiento'];
                        break;
                    }
                }
            }
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

        // Guardar Series Validadas (SerieAjena)
        foreach ($ResultsValidarSeries as $resultado) {
            $establecimiento = null;
            if ($resultsSerieArchivos) {
                foreach ($resultsSerieArchivos as $archivoData) {
                    if (in_array($resultado['serie'], $archivoData['series'])) {
                        $establecimiento = $archivoData['id_establecimiento'];
                        break;
                    }
                }
            }
            if (!$establecimiento) {
                $establecimiento = $id_establecimiento;
            }
            $fecha_registro = null;
            if (isset($resultado['fecha_registro']) && !empty($resultado['fecha_registro'])) {
                $fecha_registro = $resultado['fecha_registro'];
            } elseif (isset($resultado['fecha']) && !empty($resultado['fecha'])) {
                $fecha_registro = date('Y-m-01', strtotime($resultado['fecha']));
            } else {
                $encontrado = false;
                foreach ([$ResultsSIRE, $ResultsNUBOX, $ResultsEDSUITE] as $arrOrigen) {
                    foreach ($arrOrigen as $r) {
                        if ($r['serie'] === $resultado['serie'] && isset($r['fecha_registro'])) {
                            $fecha_registro = $r['fecha_registro'];
                            $encontrado = true;
                            break 2;
                        }
                    }
                }
                if (!$fecha_registro) {
                    $fecha_registro = date('Y-m-01');
                }
            }
            $data = [
                'serie' => $resultado['serie'],
                'conteo' => $resultado['conteo'],
                'total' => $resultado['total'],
                'fecha_registro' => $fecha_registro,
                'user_create' => $user_create,
                'user_update' => $user_update,
                'id_establecimiento' => $establecimiento,
                'estado' => 1
            ];
            SerieAjena::Insertar($data);
        }

        // Guardar VentaGlobal
        foreach ($resultsVentaGlobal as $resultado) {
            $fecha = isset($resultado['fecha']) ? date('Y-m-01', strtotime($resultado['fecha'])) : date('Y-m-01');
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
                if ($value === false) {
                    throw new Exception("Dato inválido en campo $key");
                }
            }
            VentaGlobal::Insertar($data);
        }

        // Guardar SerieSucursal
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

        // Guardar DiferenciaComprobante
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
                if ($value === false) {
                    throw new Exception("Dato inválido en campo $key");
                }
            }
            DiferenciaComprobante::Insertar($data);
        }
    }

    public function cargarArchivo($archivo, $estado, $reporte)
    {
        $tipo = ($reporte == 1) ? 'nubox' : 'edsuite';
        $uploadPath = $this->archivoService->moverArchivoSubido($archivo, $tipo);
        try {
            $respuesta = $this->apiPythonService->procesarArchivo($uploadPath, $estado);
            return $respuesta;
        } finally {
            // Limpieza opcional (si se requiere, usar $this->archivoService->limpiarCarpetaTipo($tipo))
        }
    }

    public function limpiarCarpeta($ruta)
    {
        $this->archivoService->limpiarCarpeta($ruta);
    }

    public function unirExcel($archivos)
    {
        return $this->apiPythonService->unificarExcels($archivos);
    }

    public function verificarDiferenciaComprobante($diferencias)
    {
        $diferenciasEncontradas = [];
        foreach ($diferencias as $diferencia) {
            $data = [
                'serie' => $diferencia['serie']
            ];
            $resultadosSire = $this->verificarSire($data);
            $resultadosNubox = $this->verificarNubox($data);
            $nuboxIndexado = [];
            foreach ($resultadosNubox as $item) {
                if (isset($item['numero'])) {
                    $nuboxIndexado[$item['numero']] = $item;
                }
            }
            foreach ($resultadosSire as $sireItem) {
                $num = $sireItem['numero'] ?? null;
                if ($num && isset($nuboxIndexado[$num])) {
                    if (abs($sireItem['total'] - $nuboxIndexado[$num]['total']) > 0.001) {
                        $diferenciasEncontradas[] = [
                            'serie' => $diferencia['serie'],
                            'numero' => $num,
                            'total_sire' => $sireItem['total'],
                            'total_nubox' => $nuboxIndexado[$num]['total']
                        ];
                    }
                }
            }
        }
        return $diferenciasEncontradas;
    }

    public function verificarSire($data)
    {
        $series = $data['serie'];
        $registrosEncontrados = [];
        $archivosSIRE = $this->archivoService->listarArchivosPorTipo('sire', 'sire_*.csv');
        foreach ($archivosSIRE as $archivo) {
            if (($handle = fopen($archivo, "r")) !== false) {
                $header = fgetcsv($handle);
                $colSerie = array_search('Serie del CDP', $header);
                $colNumero = array_search('Nro del CDP', $header);
                $colTotal = array_search('Importe Total', $header);
                while (($fila = fgetcsv($handle)) !== false) {
                    if ($fila[$colSerie] == $series) {
                        $registrosEncontrados[] = [
                            'numero' => $fila[$colNumero],
                            'total' => floatval($fila[$colTotal])
                        ];
                    }
                }
                fclose($handle);
            }
        }
        return $registrosEncontrados;
    }

    public function verificarNubox($data)
    {
        $seriesBuscada = $data['serie'];
        $archivosNUBOX = $this->archivoService->listarArchivosPorTipo('nubox', 'nubox_*.xlsx');
        $registrosEncontrados = [];
        foreach ($archivosNUBOX as $archivo) {
            try {
                $registros = $this->apiPythonService->verificarSerieNubox($archivo, $seriesBuscada);
                if (is_array($registros)) {
                    $registrosEncontrados = array_merge($registrosEncontrados, $registros);
                }
            } catch (Exception $e) {
                continue;
            }
        }
        return $registrosEncontrados;
    }
}
