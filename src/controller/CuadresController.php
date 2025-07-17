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
require_once __DIR__ . '/../service/CuadreService.php';
require_once __DIR__ . '/../service/ArchivoService.php';

class CuadresController
{
    private $cuadreService;
    private $archivoService;

    public function __construct()
    {
        $this->cuadreService = new CuadreService();
        $this->archivoService = new ArchivoService();
    }

    public function index()
    {
        $sms = isset($_GET['sms']) ? $_GET['sms'] : null;
        $contenido = 'view/components/cuadre.php';
        require 'view/layout.php';
    }

    public function cuadre()
    {
        try {
            $RUCSIRE = $this->cuadreService->validarRucSire($_FILES['exe_sire']['tmp_name']);
        } catch (Exception $e) {
            $ErrorSIRE = $e->getMessage();
        }
        try {
            $RUCNUBOX = $this->cuadreService->validarRucNubox($_FILES['exe_nubox']['tmp_name']);
        } catch (Exception $e) {
            $ErrorNUBOX = $e->getMessage();
        }
        if (!isset($_FILES['exe_edsuite']) || $_FILES['exe_edsuite']['error'] !== UPLOAD_ERR_OK) {
            $ErrorEDSUITE = "No se selecciono EDSUITE.";
        }
        if (isset($ErrorSIRE) || isset($ErrorNUBOX) || isset($ErrorEDSUITE)) {
            $errores = [];
            if (isset($ErrorSIRE)) $errores[] = "SIRE: " . $ErrorSIRE;
            if (isset($ErrorNUBOX)) $errores[] = "NUBOX: " . $ErrorNUBOX;
            if (isset($ErrorEDSUITE)) $errores[] = "EDSUITE: " . $ErrorEDSUITE;
            header("Location: index.php?controller=cuadres&error=" . urlencode(implode(". ", $errores)));
            exit();
        }
        $userId = SesionHelper::obtenerUsuarioActual();
        if (!$userId) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("No se pudo determinar el usuario"));
            exit();
        }
        $user = Usuario::obtenerId($userId);
        if (!$user) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("Usuario no encontrado"));
            exit();
        }
        if (SesionHelper::esModoSuperAdmin()) {
            $id_cliente = SesionHelper::obtenerClienteActual();
        } else {
            $id_cliente = $user['id_cliente'];
        }
        $cliente = Cliente::obtenerCliente($id_cliente);
        $establecimientos = Establecimiento::obtenerEstablecimientoPorCliente($id_cliente);
        $rol = $user['rol'];
        if (!$cliente) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("Cliente no encontrado"));
            exit();
        }
        if (!isset($RUCSIRE) || empty($RUCSIRE)) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("No se pudo obtener el RUC del archivo SIRE"));
            exit();
        }
        if (!isset($RUCNUBOX) || empty($RUCNUBOX)) {
            header("Location: index.php?controller=cuadres&error=" . urlencode("No se pudo obtener el RUC del archivo NUBOX"));
            exit();
        }
        $RUCCLiente = ($rol == 'Administrador') ? $RUCSIRE : $cliente['ruc'];
        if ($RUCSIRE && $RUCNUBOX) {
            if ($RUCSIRE == $RUCNUBOX) {
                if ($RUCSIRE == $RUCCLiente) {
                    // Procesar SIRE
                    // Guardar archivo SIRE usando ArchivoService
                    $uploadPathSire = $this->archivoService->moverArchivoSubido($_FILES['exe_sire'], 'sire');
                    $fileNameSire = basename($uploadPathSire);
                    $SIRE = $this->cuadreService->procesarSire($uploadPathSire, $fileNameSire);
                    $ResultsSIRE = $SIRE['ResultsSIRE'];
                    // Procesar Nubox
                    try {
                        $nuboxResponse = $this->cuadreService->cargarArchivo($_FILES['exe_nubox'], 1, 1);
                    } catch (Exception $e) {
                        $errorMsg = urlencode($e->getMessage());
                        header("Location: index.php?controller=cuadres&action=index&error={$errorMsg}");
                        exit;
                    }
                    $ResultsNUBOX = [];
                    if (isset($nuboxResponse['resultados']) && $nuboxResponse['estado'] == 1) {
                        $NUBOX = $this->cuadreService->procesarDatosNubox($nuboxResponse['resultados']);
                        $ResultsNUBOX = $NUBOX['ResultsNUBOX'];
                    }
                    // Diferencias
                    $diferencia = $this->cuadreService->diferenciaComprobante($ResultsSIRE, $ResultsNUBOX);
                    $diferenciaGlobales = [];
                    if ($diferencia != null) {
                        $diferenciaGlobales = $this->cuadreService->verificarDiferenciaComprobante($diferencia);
                        $this->archivoService->limpiarCarpeta(__DIR__ . '/../../uploads');
                    } else {
                        $this->archivoService->limpiarCarpeta(__DIR__ . '/../../uploads');
                    }
                    // Procesar EDSUITE
                    try {
                        $edsuiteResponse = $this->cuadreService->cargarArchivo($_FILES['exe_edsuite'], 2, 2);
                    } catch (Exception $e) {
                        $errorMsg = urlencode($e->getMessage());
                        header("Location: index.php?controller=cuadres&action=index&error={$errorMsg}");
                        exit;
                    }
                    $ResultsEDSUITE = [];
                    $resultsVentaGlobal = [];
                    $resultsSerieArchivos = [];
                    if (isset($edsuiteResponse['resultados']) && $edsuiteResponse['estado'] == 2) {
                        $EDSUITE = $this->cuadreService->procesarDatosEDSuite($edsuiteResponse['resultados']);
                        $ResultsEDSUITE = $EDSUITE['ResultsEDSUITE'];
                        $resultsVentaGlobal = $edsuiteResponse['resultados_productos'] ?? [];
                        $resultsSerieArchivos = $edsuiteResponse['resultados_archivo'] ?? [];
                        $this->archivoService->limpiarCarpeta(__DIR__ . '/../../uploads');
                    } elseif (isset($edsuiteResponse['message'])) {
                        $ErrorEDSUITE = $edsuiteResponse['message'];
                        $this->archivoService->limpiarCarpeta(__DIR__ . '/../../uploads');
                    }
                    // Validar series
                    $validarSeries = $this->cuadreService->validarSeries($ResultsSIRE, $ResultsNUBOX, $ResultsEDSUITE);
                    $ResultsValidarSeries = $validarSeries['ResultsValidarSeries'];
                } else {
                    $ErrorNUBOX = "Los RUC de los archivos no pertenece a la empresa.";
                }
            } else {
                $ErrorSIRE = "Los RUC de los archivos no coinciden.";
            }
        } else {
            $ErrorSIRE = "No se subieron archivos válidos.";
        }
        $seriesEstablecimientos = [];
        foreach ($establecimientos as &$establecimiento) {
            $series = SerieSucursal::obtenerSeriesPorEstablecimiento($establecimiento['id']);
            if ($series && isset($series['serie'])) {
                $establecimiento['serie'] = $series['serie'];
                $seriesEstablecimientos[] = $series['serie'];
            } else {
                $establecimiento['serie'] = null;
            }
        }
        unset($establecimiento);

        if (empty($resultsSerieArchivos)) {
            $resultsSerieArchivos = [];
            $todasSeries = [];
            foreach ([$ResultsSIRE, $ResultsNUBOX, $ResultsEDSUITE] as $arr) {
                foreach ($arr as $item) {
                    if (isset($item['serie'])) {
                        $todasSeries[$item['serie']] = true;
                    }
                }
            }
            foreach (array_keys($todasSeries) as $serie) {
                $resultsSerieArchivos[] = [
                    'series' => [$serie],
                ];
            }
        }

        $coincidentes = [];
        foreach ($resultsSerieArchivos as $archivo) {
            foreach ($archivo['series'] as $serieArchivo) {
                if (in_array($serieArchivo, $seriesEstablecimientos)) {
                    $coincidentes[] = $serieArchivo;
                }
            }
        }

        $_SESSION['ResultsSIRE'] = $ResultsSIRE ?? [];
        $_SESSION['ResultsNUBOX'] = $ResultsNUBOX ?? [];
        $_SESSION['ResultsEDSUITE'] = $ResultsEDSUITE ?? [];
        $_SESSION['ResultsValidarSeries'] = $ResultsValidarSeries ?? [];
        $_SESSION['resultsVentaGlobal'] = $resultsVentaGlobal ?? [];
        $_SESSION['diferenciaGlobales'] = $diferenciaGlobales ?? [];
        // Si NO hay coincidencias, mostrar el modal para asignar establecimiento
        if (empty($coincidentes)) {
            $_SESSION['resultsSerieArchivos'] = $resultsSerieArchivos;
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
        $userId = SesionHelper::obtenerUsuarioActual();
        $user = Usuario::obtenerId($userId);
        if (!$user) {
            throw new Exception("No se pudo obtener información del usuario");
        }
        $id_establecimiento = SesionHelper::obtenerEstablecimientoActual();
        $sire = $_SESSION['ResultsSIRE'][0]['fecha_registro'];
        $existeFecha = Cuadre::existeFecha($sire, $id_establecimiento);
        if (!$existeFecha) {
            $this->cuadreService->guardarBD(
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
            $archivo = $this->cuadreService->unirExcel($_FILES['archivos_excel']);
            header("Location: index.php?controller=cuadres&modal=unificacionExitosa&archivo=" . urlencode($archivo));
            exit;
        } catch (Exception $e) {
            $errorMsg = urlencode($e->getMessage());
            header("Location: index.php?controller=cuadres&action=index&error={$errorMsg}");
            exit;
        }
    }

    public function cargar_archivo($archivo, $estado, $reporte)
    {
        $tipo = ($reporte == 1) ? 'nubox' : 'edsuite';
        $uploadPath = $this->archivoService->moverArchivoSubido($archivo, $tipo);
        try {
            $respuesta = $this->cuadreService->cargarArchivo($archivo, $estado, $reporte);
            return $respuesta;
        } finally {
            // Si se requiere limpieza, usar $this->archivoService->limpiarCarpetaTipo($tipo);
        }
    }
}
