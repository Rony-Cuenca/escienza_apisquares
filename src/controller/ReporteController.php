<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../model/Cuadre.php';
require_once __DIR__ . '/../model/Establecimiento.php';
require_once __DIR__ . '/../model/SerieAjena.php';
require_once __DIR__ . '/../model/VentaGlobal.php';
require_once __DIR__ . '/../helpers/permisos_helper.php';
require_once __DIR__ . '/../helpers/sesion_helper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteController
{
    private function verificarPermisosCompletos()
    {
        if (!tieneAccesoCompleto()) {
            $_SESSION['mensaje'] = "No tienes permisos para realizar esta acción.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: index.php?controller=home&action=index");
            exit();
        }
    }

    private function verificarPermisosGeneracion()
    {
        if (!puedeGenerarReportes()) {
            $_SESSION['mensaje'] = "No tienes permisos para generar reportes.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: index.php?controller=home&action=index");
            exit();
        }
    }

    private function verificarPermisosVisualizacion()
    {
        if (!puedeVerReportes()) {
            $_SESSION['mensaje'] = "No tienes permisos para ver reportes.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: index.php?controller=home&action=index");
            exit();
        }
    }

    private function obtenerUsuarioActualSeguro()
    {
        return SesionHelper::obtenerUsuarioActual();
    }

    private function verificarSesion()
    {
        if (!SesionHelper::obtenerClienteActual()) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    public function index()
    {
        $this->verificarSesion();
        $this->verificarPermisosVisualizacion();

        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();
        $mesSeleccionado = $_GET['mes'] ?? '';

        list($id_cliente, $establecimientos) = $this->getClienteYEstablecimientos();

        $datosReporte = $this->obtenerDatosReporte($mesSeleccionado);
        extract($datosReporte);

        $contenido = 'view/components/reporte.php';
        require 'view/layout.php';
    }

    private function procesarSeriesConNotasCredito($cuadres)
    {
        $resultado = [];
        foreach ($cuadres as $c) {
            $monto = isset($c['monto_total']) ? (float)$c['monto_total'] : 0;
            $nuevo = $c;
            $nuevo['total'] = $monto < 0 ? 0 : $monto;
            $nuevo['nota_credito'] = $monto < 0 ? $monto : 0;
            $resultado[] = $nuevo;
        }
        return $resultado;
    }

    private function getClienteYEstablecimientos()
    {
        $id_cliente = SesionHelper::obtenerClienteActual();
        $establecimientos = $id_cliente ? Establecimiento::obtenerEstablecimientoPorCliente($id_cliente) : [];
        return [$id_cliente, $establecimientos];
    }

    private function obtenerDatosReporte($mesSeleccionado)
    {
        $cuadresSIRE = [];
        $cuadresNUBOX = [];
        $cuadresEDSUITE = [];
        $totalesTipoDoc = [];
        $seriesTotales = [];
        $seriesAjenas = [];
        $ventasGlobales = [];
        $diferenciasNuboxSire = [];
        $diferenciasTipoDocNuboxSire = [];
        $seriesEdSuite = [];
        $id_establecimiento = $_GET['id_establecimiento'] ?? '';
        $seriesEstablecimiento = [];

        if ($id_establecimiento) {
            require_once __DIR__ . '/../model/SerieSucursal.php';
            $seriesEstablecimiento = SerieSucursal::obtenerTodasLasSeriesPorEstablecimiento($id_establecimiento);
            if ($id_establecimiento && empty($seriesEstablecimiento)) {
                return [
                    'cuadresSIRE' => [],
                    'cuadresNUBOX' => [],
                    'cuadresEDSUITE' => [],
                    'totalesTipoDoc' => [],
                    'seriesTotales' => [],
                    'diferenciasNuboxSire' => [],
                    'diferenciasTipoDocNuboxSire' => [],
                    'seriesAjenas' => [],
                    'ventasGlobales' => [],
                    'seriesEdSuite' => []
                ];
            }
        }

        if ($mesSeleccionado) {
            $cuadres = Cuadre::obtenerCuadresPorMes($mesSeleccionado);
            list($cuadresSIRE, $cuadresNUBOX, $cuadresEDSUITE) = $this->separarCuadresPorSistemaExcluyendoAjenas($cuadres, $mesSeleccionado);

            $cuadresNUBOX = $this->procesarSeriesConNotasCredito($cuadresNUBOX);
            $cuadresEDSUITE = $this->procesarSeriesConNotasCredito($cuadresEDSUITE);
            $cuadresSIRE = $this->procesarSeriesConNotasCredito($cuadresSIRE);
            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoComprobante($mesSeleccionado, $id_establecimiento ?: null);
            $seriesTotales = Cuadre::obtenerTotalesPorSerieExcluyendoAjenas($mesSeleccionado);
            $seriesAjenas = SerieAjena::obtenerPorMes($mesSeleccionado);
            $ventasGlobales = VentaGlobal::obtenerPorMes($mesSeleccionado);
            $diferencias = $this->calcularDiferenciasNuboxSire($seriesTotales, $totalesTipoDoc);
            $diferenciasNuboxSire = $diferencias['diferenciasNuboxSire'];
            $diferenciasTipoDocNuboxSire = $diferencias['diferenciasTipoDocNuboxSire'];
            $seriesEdSuite = $this->calcularDiferenciasNuboxEdSuite($cuadresEDSUITE, $seriesTotales);

            // FILTRADO POR ESTABLECIMIENTO
            if ($id_establecimiento && !empty($seriesEstablecimiento)) {
                $cuadresNUBOX = array_filter($cuadresNUBOX, fn($c) => in_array($c['serie'], $seriesEstablecimiento));
                $cuadresEDSUITE = array_filter($cuadresEDSUITE, fn($c) => in_array($c['serie'], $seriesEstablecimiento));
                $cuadresSIRE = array_filter($cuadresSIRE, fn($c) => in_array($c['serie'], $seriesEstablecimiento));
                $seriesEdSuite = array_filter($seriesEdSuite, fn($c) => in_array($c['serie'], $seriesEstablecimiento));
                $seriesTotales = array_filter($seriesTotales, fn($k) => in_array($k, $seriesEstablecimiento), ARRAY_FILTER_USE_KEY);
                $diferenciasNuboxSire = array_filter($diferenciasNuboxSire, fn($row) => in_array($row['serie'], $seriesEstablecimiento));
                if (!empty($ventasGlobales) && isset($ventasGlobales[0]['serie'])) {
                    $ventasGlobales = array_filter($ventasGlobales, fn($v) => in_array($v['serie'], $seriesEstablecimiento));
                }
            }
        }

        return compact(
            'cuadresSIRE',
            'cuadresNUBOX',
            'cuadresEDSUITE',
            'totalesTipoDoc',
            'seriesTotales',
            'diferenciasNuboxSire',
            'diferenciasTipoDocNuboxSire',
            'seriesAjenas',
            'ventasGlobales',
            'seriesEdSuite'
        );
    }

    private function calcularDiferenciasNuboxEdSuite($cuadresEDSUITE, $seriesTotales)
    {
        $resultado = [];
        foreach ($cuadresEDSUITE as $c) {
            $serie = $c['serie'] ?? '';
            $totalNubox = $seriesTotales[$serie][1] ?? 0;
            $totalEdSuite = $seriesTotales[$serie][3] ?? 0;
            $notaCredito = $c['nota_credito'] ?? 0;
            $resultado[] = [
                'serie' => $serie,
                'combustible' => $totalEdSuite,
                'extras' => 0,
                'nota_credito' => $notaCredito,
                'diferencia' => $totalNubox - $totalEdSuite
            ];
        }
        return $resultado;
    }

    private function separarCuadresPorSistemaExcluyendoAjenas($cuadres, $mesSeleccionado)
    {
        $seriesAjenasArray = SerieAjena::obtenerPorMes($mesSeleccionado);
        $seriesAjenasLista = array_column($seriesAjenasArray, 'serie');

        $sire = [];
        $nubox = [];
        $edsuite = [];
        foreach ($cuadres as $cuadre) {
            if (in_array($cuadre['serie'], $seriesAjenasLista)) {
                continue;
            }
            if ($cuadre['id_reporte'] == 2) {
                $sire[] = $cuadre;
            } elseif ($cuadre['id_reporte'] == 1) {
                $nubox[] = $cuadre;
            } elseif ($cuadre['id_reporte'] == 3) {
                $edsuite[] = $cuadre;
            }
        }
        return [$sire, $nubox, $edsuite];
    }

    private function calcularDiferenciasNuboxSire($seriesTotales, $totalesTipoDoc)
    {
        $diferenciasNuboxSire = [];
        foreach (array_unique(array_keys($seriesTotales)) as $serie) {
            $totalNUBOX = $seriesTotales[$serie][1] ?? 0;
            $totalSIRE = $seriesTotales[$serie][2] ?? 0;
            $diferenciasNuboxSire[$serie] = [
                'serie' => $serie,
                'total_nubox' => $totalNUBOX,
                'total_sire' => $totalSIRE,
                'diferencia' => $totalNUBOX - $totalSIRE
            ];
        }

        $diferenciasTipoDocNuboxSire = [];
        foreach (array_unique(array_keys($totalesTipoDoc)) as $tipo) {
            $totalNUBOX = $totalesTipoDoc[$tipo][1] ?? 0;
            $totalSIRE = $totalesTipoDoc[$tipo][2] ?? 0;
            $diferenciasTipoDocNuboxSire[$tipo] = [
                'tipo' => $tipo,
                'total_nubox' => $totalNUBOX,
                'total_sire' => $totalSIRE,
                'diferencia' => $totalNUBOX - $totalSIRE
            ];
        }
        return [
            'diferenciasNuboxSire' => $diferenciasNuboxSire,
            'diferenciasTipoDocNuboxSire' => $diferenciasTipoDocNuboxSire
        ];
    }

    private function obtenerNombreMes($mesSeleccionado, $mesesDisponibles)
    {
        $nombreMes = $mesSeleccionado;
        foreach ($mesesDisponibles as $mes) {
            if ($mes['mes'] == $mesSeleccionado) {
                $nombreMes = $mes['mes_nombre'];
                break;
            }
        }
        $meses = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre'
        ];
        foreach ($meses as $en => $es) {
            if (strpos($nombreMes, $en) !== false) {
                $nombreMes = str_replace($en, $es, $nombreMes);
                break;
            }
        }
        return $nombreMes;
    }

    public function exportarPDF()
    {
        $this->verificarSesion();
        $this->verificarPermisosGeneracion();

        $mesSeleccionado = $_GET['mes'] ?? '';
        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();
        $usuarioNombre = SesionHelper::obtenerNombreUsuario();
        list($id_cliente, $establecimientos) = $this->getClienteYEstablecimientos();

        $id_establecimientos = $_GET['id_establecimientos'] ?? [];
        if (!is_array($id_establecimientos)) {
            $id_establecimientos = [$id_establecimientos];
        }
        $esGlobal = isset($_GET['global']) && $_GET['global'] == '1';

        $htmlFinal = '';
        $nombreArchivo = '';
        $fechaActual = date('Y-m-d');
        $nombreMes = $this->obtenerNombreMes($mesSeleccionado, $mesesDisponibles);

        if ($esGlobal) {
            // Reporte Global: un solo PDF consolidado
            $rucEstablecimiento = '';
            $nombreEstablecimiento = '';
            if ($id_cliente) {
                $cliente = \Establecimiento::obtenerClientePorId($id_cliente);
                if ($cliente) {
                    $rucEstablecimiento = $cliente['ruc'] ?? '';
                    $nombreEstablecimiento = $cliente['razon_social'] ?? '';
                }
            }
            unset($_GET['id_establecimiento']);
            $datosReporte = $this->obtenerDatosReporte($mesSeleccionado);
            extract($datosReporte);

            ob_start();
            include __DIR__ . '/../view/components/reporte_pdf.php';
            $htmlFinal = ob_get_clean();
            $nombreArchivo = "Reporte_Global_{$nombreMes}_{$fechaActual}.pdf";
        } else if (empty($id_establecimientos)) {
            $id_establecimiento = $_GET['id_establecimiento'] ?? '';
            $rucEstablecimiento = '';
            $nombreEstablecimiento = '';

            if ($id_establecimiento) {
                $establecimiento = \Establecimiento::obtenerEstablecimiento($id_establecimiento);
                if ($establecimiento) {
                    $nombreEstablecimiento = $establecimiento['etiqueta'] ?? '';
                    $id_cliente = $establecimiento['id_cliente'] ?? null;
                    if ($id_cliente) {
                        $cliente = \Establecimiento::obtenerClientePorId($id_cliente);
                        $rucEstablecimiento = $cliente['ruc'] ?? '';
                    }
                }
            } else {
                if ($id_cliente) {
                    $cliente = \Establecimiento::obtenerClientePorId($id_cliente);
                    if ($cliente) {
                        $rucEstablecimiento = $cliente['ruc'] ?? '';
                        $nombreEstablecimiento = $cliente['razon_social'] ?? '';
                    }
                }
            }

            $datosReporte = $this->obtenerDatosReporte($mesSeleccionado);
            extract($datosReporte);

            ob_start();
            include __DIR__ . '/../view/components/reporte_pdf.php';
            $htmlFinal = ob_get_clean();
            $nombreArchivo = $id_establecimiento
                ? "Reporte {$nombreEstablecimiento} - {$nombreMes} - {$fechaActual}.pdf"
                : "Reporte General {$nombreEstablecimiento} - {$nombreMes} - {$fechaActual}.pdf";
        } else {
            foreach ($id_establecimientos as $id_estab) {
                $establecimiento = \Establecimiento::obtenerEstablecimiento($id_estab);
                $nombreEstablecimiento = $establecimiento['etiqueta'] ?? '';
                $id_cliente = $establecimiento['id_cliente'] ?? null;
                $rucEstablecimiento = '';
                if ($id_cliente) {
                    $cliente = \Establecimiento::obtenerClientePorId($id_cliente);
                    $rucEstablecimiento = $cliente['ruc'] ?? '';
                }
                $_GET['id_establecimiento'] = $id_estab;
                $datosReporte = $this->obtenerDatosReporte($mesSeleccionado);
                extract($datosReporte);
                ob_start();
                include __DIR__ . '/../view/components/reporte_pdf.php';
                $htmlEstab = ob_get_clean();

                if ($htmlFinal === '') {
                    $htmlFinal = $htmlEstab;
                } else {
                    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $htmlEstab, $matches)) {
                        $htmlFinal = preg_replace('/<\/body>\s*<\/html>\s*$/is', '', $htmlFinal);
                        $htmlFinal .= '<div style="page-break-before:always"></div>' . $matches[1] . '</body></html>';
                    }
                }
            }
            $nombreArchivo = "Reporte_{$nombreMes}_{$fechaActual}.pdf";
        }

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($htmlFinal);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->render();
        $dompdf->stream($nombreArchivo);
        exit;
    }

    private function limpiarNombreHoja($nombre)
    {
        $nombre = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', '-', $nombre);
        $nombre = preg_replace('/\\s+/', ' ', $nombre);
        return mb_substr(trim($nombre), 0, 31);
    }

    public function exportarExcel()
    {
        $this->verificarSesion();
        $this->verificarPermisosGeneracion();


        $mesSeleccionado = $_GET['mes'] ?? '';
        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();
        $usuarioNombre = SesionHelper::obtenerNombreUsuario();
        $id_establecimientos = $_GET['id_establecimientos'] ?? [];
        if (!is_array($id_establecimientos)) {
            $id_establecimientos = [$id_establecimientos];
        }
        $esGlobal = isset($_GET['global']) && $_GET['global'] == '1';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $nombreMes = $this->obtenerNombreMes($mesSeleccionado, $mesesDisponibles);
        $fechaActual = date('d/m/Y H:i');

        $headerPrincipalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0f172a']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1e293b']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $headerSecundarioStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1d4ed8']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $separadorStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0f172a']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f8fafc']],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '2563eb']],
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cbd5e1']]
            ],
            'alignment' => ['horizontal' => 'left', 'vertical' => 'center']
        ];
        $yellowHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '0f172a'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fcd34d']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'f59e0b']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $blueHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1e40af']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $greenTotalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $redStyle = [
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dc2626']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'b91c1c']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $borderStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ffffff']]
        ];
        $warningStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '0f172a'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fed7aa']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'ea580c']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $infoStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0369a1']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0284c7']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $neutralStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '1f2937'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f3f4f6']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9ca3af']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $totalEspecialStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1f2937']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        if ($esGlobal) {
            $id_cliente = SesionHelper::obtenerClienteActual();
            $cliente = \Establecimiento::obtenerClientePorId($id_cliente);
            $rucEstablecimiento = $cliente['ruc'] ?? '';
            $nombreEstablecimiento = $cliente['razon_social'] ?? '';
            unset($_GET['id_establecimiento']);
            $datosReporte = $this->obtenerDatosReporte($mesSeleccionado);
            $cuadresSIRE = $datosReporte['cuadresSIRE'];
            $cuadresNUBOX = $datosReporte['cuadresNUBOX'];
            $cuadresEDSUITE = $datosReporte['cuadresEDSUITE'];
            $totalesTipoDoc = $datosReporte['totalesTipoDoc'];
            $seriesTotales = $datosReporte['seriesTotales'];
            $diferenciasSeries = $datosReporte['diferenciasNuboxSire'];
            $seriesAjenas = $datosReporte['seriesAjenas'];
            $ventasGlobales = $datosReporte['ventasGlobales'];
            $seriesEdSuite = $datosReporte['seriesEdSuite'];

            $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Reporte Global');
            $spreadsheet->addSheet($sheet);
            $row = 6;
            $this->SeccionCabecera($sheet, $nombreMes, $nombreEstablecimiento, $rucEstablecimiento, $usuarioNombre, $headerPrincipalStyle, $headerSecundarioStyle);
            $this->SeccionSeparador($sheet, $row, "ANÁLISIS COMPARATIVO POR SISTEMA DE FACTURACIÓN", $separadorStyle);
            $row += 2;
            $this->SeccionResumenSoftware($sheet, $row, $cuadresNUBOX, $cuadresEDSUITE, $cuadresSIRE, $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle, $warningStyle);
            $row += 1;
            $this->SeccionSeparador($sheet, $row, "RESUMEN DE COMPROBANTES", $separadorStyle);
            $row += 2;
            $this->SeccionResumenComprobante($sheet, $row, $totalesTipoDoc, $yellowHeaderStyle, $greenTotalStyle, $redStyle, $borderStyle, $infoStyle);
            $row += 1;
            $this->SeccionSeparador($sheet, $row, "REPORTES GLOBALES Y SERIES AJENAS", $separadorStyle);
            $row += 2;
            $this->SeccionReportesGlobales($sheet, $row, $cuadresNUBOX, $seriesAjenas, $ventasGlobales, $mesSeleccionado, $seriesEdSuite, $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle);
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(12);
            foreach (range('C', 'R') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $sheet->getColumnDimension($col)->setWidth(max(10, min(25, $sheet->getColumnDimension($col)->getWidth())));
            }
            for ($i = 6; $i <= $row; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(18);
            }
            $sheet->getStyle("C6:R{$row}")->getNumberFormat()->setFormatCode('_("S/" * #,##0.00_);_("S/" * \(,#,##0.00\);_("S/" * 0.00_);_(@_)');
            $sheet->getStyle("B6:B{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
            $sheet->getStyle("J6:J{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
            $sheet->getStyle("N6:N{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
            $sheet->getStyle("A6:A{$row}")->getAlignment()->setWrapText(true);
            $columnasMomentarias = ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'O', 'P', 'Q', 'R'];
            foreach ($columnasMomentarias as $col) {
                $sheet->getStyle("{$col}6:{$col}{$row}")->getNumberFormat()->setFormatCode('_("S/" * #,##0.00_);_("S/" * \(,#,##0.00\);_("S/" * 0.00_);_(@_)');
            }
            $columnasUnidades = ['J', 'N'];
            foreach ($columnasUnidades as $col) {
                $sheet->getStyle("{$col}6:{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
            }
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                ->setScale(95);
            $sheet->getPageMargins()
                ->setTop(1.2)
                ->setRight(0.8)
                ->setLeft(0.8)
                ->setBottom(1.2)
                ->setHeader(0.5)
                ->setFooter(0.5);
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);
            $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
            $footer = '&L&"Arial,Bold,9"ESCIENZA - SISTEMA DE GESTIÓN EMPRESARIAL&C&"Arial,8"Reporte de Conciliación de Ventas&R&"Arial,8"Generado: ' . $fechaActual . ' - Página &P de &N';
            $sheet->getHeaderFooter()->setOddFooter($footer);
            $header = '&C&"Arial,Bold,10"REPORTE EJECUTIVO DE CONCILIACIÓN DE VENTAS';
            $sheet->getHeaderFooter()->setOddHeader($header);
        } else if (empty($id_establecimientos)) {
            $id_cliente = SesionHelper::obtenerClienteActual();
            $cliente = \Establecimiento::obtenerClientePorId($id_cliente);
            $rucEstablecimiento = $cliente['ruc'] ?? '';
            $nombreEstablecimiento = $cliente['razon_social'] ?? '';
            $datosReporte = $this->obtenerDatosReporte($mesSeleccionado);
            $cuadresSIRE = $datosReporte['cuadresSIRE'];
            $cuadresNUBOX = $datosReporte['cuadresNUBOX'];
            $cuadresEDSUITE = $datosReporte['cuadresEDSUITE'];
            $totalesTipoDoc = $datosReporte['totalesTipoDoc'];
            $seriesTotales = $datosReporte['seriesTotales'];
            $diferenciasSeries = $datosReporte['diferenciasNuboxSire'];
            $seriesAjenas = $datosReporte['seriesAjenas'];
            $ventasGlobales = $datosReporte['ventasGlobales'];
            $seriesEdSuite = $datosReporte['seriesEdSuite'];

            $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Reporte General');
            $spreadsheet->addSheet($sheet);
            $row = 6;
            $this->SeccionCabecera($sheet, $nombreMes, $nombreEstablecimiento ?? '', $rucEstablecimiento ?? '', $usuarioNombre, $headerPrincipalStyle, $headerSecundarioStyle);
            $this->SeccionSeparador($sheet, $row, "ANÁLISIS COMPARATIVO POR SISTEMA DE FACTURACIÓN", $separadorStyle);
            $row += 2;
            $this->SeccionResumenSoftware($sheet, $row, $cuadresNUBOX ?? [], $cuadresEDSUITE ?? [], $cuadresSIRE ?? [], $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle, $warningStyle);
            $row += 1;
            $this->SeccionSeparador($sheet, $row, "RESUMEN DE COMPROBANTES", $separadorStyle);
            $row += 2;
            $this->SeccionResumenComprobante($sheet, $row, $totalesTipoDoc ?? [], $yellowHeaderStyle, $greenTotalStyle, $redStyle, $borderStyle, $infoStyle);
            $row += 1;
            $this->SeccionSeparador($sheet, $row, "REPORTES GLOBALES Y SERIES AJENAS", $separadorStyle);
            $row += 2;
            $this->SeccionReportesGlobales($sheet, $row, $cuadresNUBOX ?? [], $seriesAjenas ?? [], $ventasGlobales ?? [], $mesSeleccionado, $seriesEdSuite ?? [], $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle);
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(12);
            foreach (range('C', 'R') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $sheet->getColumnDimension($col)->setWidth(max(10, min(25, $sheet->getColumnDimension($col)->getWidth())));
            }
            for ($i = 6; $i <= $row; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(18);
            }
            $sheet->getStyle("C6:R{$row}")->getNumberFormat()->setFormatCode('_("S/" * #,##0.00_);_("S/" * \(,#,##0.00\);_("S/" * 0.00_);_(@_)');
            $sheet->getStyle("B6:B{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
            $sheet->getStyle("J6:J{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
            $sheet->getStyle("N6:N{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
            $sheet->getStyle("A6:A{$row}")->getAlignment()->setWrapText(true);
            $columnasMomentarias = ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'O', 'P', 'Q', 'R'];
            foreach ($columnasMomentarias as $col) {
                $sheet->getStyle("{$col}6:{$col}{$row}")->getNumberFormat()->setFormatCode('_("S/" * #,##0.00_);_("S/" * \(,#,##0.00\);_("S/" * 0.00_);_(@_)');
            }
            $columnasUnidades = ['J', 'N'];
            foreach ($columnasUnidades as $col) {
                $sheet->getStyle("{$col}6:{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
            }
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                ->setScale(95);
            $sheet->getPageMargins()
                ->setTop(1.2)
                ->setRight(0.8)
                ->setLeft(0.8)
                ->setBottom(1.2)
                ->setHeader(0.5)
                ->setFooter(0.5);
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);
            $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
            $footer = '&L&"Arial,Bold,9"ESCIENZA - SISTEMA DE GESTIÓN EMPRESARIAL&C&"Arial,8"Reporte de Conciliación de Ventas&R&"Arial,8"Generado: ' . $fechaActual . ' - Página &P de &N';
            $sheet->getHeaderFooter()->setOddFooter($footer);
            $header = '&C&"Arial,Bold,10"REPORTE EJECUTIVO DE CONCILIACIÓN DE VENTAS';
            $sheet->getHeaderFooter()->setOddHeader($header);
        } else {
            foreach ($id_establecimientos as $id_estab) {
                $establecimiento = \Establecimiento::obtenerEstablecimiento($id_estab);
                $nombreEstablecimiento = $establecimiento['etiqueta'] ?? 'Establecimiento';
                $id_cliente = $establecimiento['id_cliente'] ?? null;
                $rucEstablecimiento = '';
                if ($id_cliente) {
                    $cliente = \Establecimiento::obtenerClientePorId($id_cliente);
                    $rucEstablecimiento = $cliente['ruc'] ?? '';
                }
                $_GET['id_establecimiento'] = $id_estab;
                $datosReporte = $this->obtenerDatosReporte($mesSeleccionado);
                $cuadresSIRE = $datosReporte['cuadresSIRE'];
                $cuadresNUBOX = $datosReporte['cuadresNUBOX'];
                $cuadresEDSUITE = $datosReporte['cuadresEDSUITE'];
                $totalesTipoDoc = $datosReporte['totalesTipoDoc'];
                $seriesTotales = $datosReporte['seriesTotales'];
                $diferenciasSeries = $datosReporte['diferenciasNuboxSire'];
                $seriesAjenas = $datosReporte['seriesAjenas'];
                $ventasGlobales = $datosReporte['ventasGlobales'];
                $seriesEdSuite = $datosReporte['seriesEdSuite'];

                $nombreHoja = $this->limpiarNombreHoja($nombreEstablecimiento);
                $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $nombreHoja);
                $spreadsheet->addSheet($sheet);
                $row = 6;
                $this->SeccionCabecera($sheet, $nombreMes, $nombreEstablecimiento, $rucEstablecimiento, $usuarioNombre, $headerPrincipalStyle, $headerSecundarioStyle);
                $this->SeccionSeparador($sheet, $row, "ANÁLISIS COMPARATIVO POR SISTEMA DE FACTURACIÓN", $separadorStyle);
                $row += 2;
                $this->SeccionResumenSoftware($sheet, $row, $cuadresNUBOX, $cuadresEDSUITE, $cuadresSIRE, $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle, $warningStyle);
                $row += 1;
                $this->SeccionSeparador($sheet, $row, "RESUMEN DE COMPROBANTES", $separadorStyle);
                $row += 2;
                $this->SeccionResumenComprobante($sheet, $row, $totalesTipoDoc, $yellowHeaderStyle, $greenTotalStyle, $redStyle, $borderStyle, $infoStyle);
                $row += 1;
                $this->SeccionSeparador($sheet, $row, "REPORTES GLOBALES Y SERIES AJENAS", $separadorStyle);
                $row += 2;
                $this->SeccionReportesGlobales($sheet, $row, $cuadresNUBOX, $seriesAjenas, $ventasGlobales, $mesSeleccionado, $seriesEdSuite, $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle);
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(12);
                foreach (range('C', 'R') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getColumnDimension($col)->setWidth(max(10, min(25, $sheet->getColumnDimension($col)->getWidth())));
                }
                for ($i = 6; $i <= $row; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(18);
                }
                $sheet->getStyle("C6:R{$row}")->getNumberFormat()->setFormatCode('_("S/" * #,##0.00_);_("S/" * \(,#,##0.00\);_("S/" * 0.00_);_(@_)');
                $sheet->getStyle("B6:B{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
                $sheet->getStyle("J6:J{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
                $sheet->getStyle("N6:N{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
                $sheet->getStyle("A6:A{$row}")->getAlignment()->setWrapText(true);
                $columnasMomentarias = ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'O', 'P', 'Q', 'R'];
                foreach ($columnasMomentarias as $col) {
                    $sheet->getStyle("{$col}6:{$col}{$row}")->getNumberFormat()->setFormatCode('_("S/" * #,##0.00_);_("S/" * \(,#,##0.00\);_("S/" * 0.00_);_(@_)');
                }
                $columnasUnidades = ['J', 'N'];
                foreach ($columnasUnidades as $col) {
                    $sheet->getStyle("{$col}6:{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
                }
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setScale(95);
                $sheet->getPageMargins()
                    ->setTop(1.2)
                    ->setRight(0.8)
                    ->setLeft(0.8)
                    ->setBottom(1.2)
                    ->setHeader(0.5)
                    ->setFooter(0.5);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);
                $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
                $fechaActual = date('d/m/Y H:i');
                $footer = '&L&"Arial,Bold,9"ESCIENZA - SISTEMA DE GESTIÓN EMPRESARIAL&C&"Arial,8"Reporte de Conciliación de Ventas&R&"Arial,8"Generado: ' . $fechaActual . ' - Página &P de &N';
                $sheet->getHeaderFooter()->setOddFooter($footer);
                $header = '&C&"Arial,Bold,10"REPORTE EJECUTIVO DE CONCILIACIÓN DE VENTAS';
                $sheet->getHeaderFooter()->setOddHeader($header);
            }
        }
        $spreadsheet->setActiveSheetIndex(0);
        if ($esGlobal) {
            $nombreArchivo = 'Reporte_Global_' . $nombreMes . '_' . date('Y-m-d') . '.xlsx';
        } else if (empty($id_establecimientos)) {
            $nombreArchivo = 'Reporte_General_' . $nombreMes . '_' . date('Y-m-d') . '.xlsx';
        } else {
            $nombreArchivo = 'Reporte_' . $nombreMes . '_' . date('Y-m-d') . '.xlsx';
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function SeccionResumenSoftware(
        $sheet,
        &$row,
        $cuadresNUBOX,
        $cuadresEDSUITE,
        $cuadresSIRE,
        $yellowHeaderStyle,
        $blueHeaderStyle,
        $greenTotalStyle,
        $borderStyle,
        $redStyle,
        $warningStyle
    ) {
        $startRow = $row;
        $nuboxPorSerie = [];
        $edsuitePorSerie = [];
        $sirePorSerie = [];

        foreach ($cuadresNUBOX as $cuadre) {
            $serie = $cuadre['serie'];
            if (!isset($nuboxPorSerie[$serie])) {
                $nuboxPorSerie[$serie] = ['total' => 0, 'notas' => 0];
            }
            if ($cuadre['tipo_comprobante'] == 3) {
                $nuboxPorSerie[$serie]['notas'] += $cuadre['monto_total'];
            } else {
                $nuboxPorSerie[$serie]['total'] += $cuadre['monto_total'];
            }
        }

        foreach ($cuadresEDSUITE as $cuadre) {
            $serie = $cuadre['serie'];
            if (!isset($edsuitePorSerie[$serie])) {
                $edsuitePorSerie[$serie] = ['total' => 0, 'notas' => 0];
            }
            if ($cuadre['tipo_comprobante'] == 3) {
                $edsuitePorSerie[$serie]['notas'] += $cuadre['monto_total'];
            } else {
                $edsuitePorSerie[$serie]['total'] += $cuadre['monto_total'];
            }
        }

        foreach ($cuadresSIRE as $cuadre) {
            $serie = $cuadre['serie'];
            if (!isset($sirePorSerie[$serie])) {
                $sirePorSerie[$serie] = ['total' => 0, 'notas' => 0];
            }
            if ($cuadre['tipo_comprobante'] == 3) {
                $sirePorSerie[$serie]['notas'] += $cuadre['monto_total'];
            } else {
                $sirePorSerie[$serie]['total'] += $cuadre['monto_total'];
            }
        }

        // TABLA NUBOX (C-E)
        $sheet->setCellValue("C$row", "NUBOX");
        $sheet->mergeCells("C$row:E$row");
        $sheet->getStyle("C$row:E$row")->applyFromArray($yellowHeaderStyle);
        $sheet->getStyle("C$row:E$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C$row:E$row")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->setCellValue("C" . ($row + 1), "SERIES");
        $sheet->setCellValue("D" . ($row + 1), "TOTAL");
        $sheet->setCellValue("E" . ($row + 1), "NOTA DE CREDITO");
        $sheet->getStyle("C" . ($row + 1) . ":E" . ($row + 1))->applyFromArray($blueHeaderStyle);
        $sheet->getStyle("C" . ($row + 1) . ":E" . ($row + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C" . ($row + 1) . ":E" . ($row + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $currentRow = $row + 2;
        $totalNubox = 0;
        $totalNotasNubox = 0;

        if (!empty($cuadresNUBOX)) {
            foreach ($cuadresNUBOX as $cuadre) {
                $sheet->setCellValue("C$currentRow", $cuadre['serie']);
                if ($cuadre['tipo_comprobante'] == 3) {
                    $sheet->setCellValue("D$currentRow", 0);
                    $sheet->setCellValue("E$currentRow", $cuadre['monto_total']);
                    $totalNotasNubox += $cuadre['monto_total'];
                } else {
                    $sheet->setCellValue("D$currentRow", $cuadre['monto_total']);
                    $sheet->setCellValue("E$currentRow", 0);
                    $totalNubox += $cuadre['monto_total'];
                }
                $sheet->getStyle("C$currentRow:E$currentRow")->applyFromArray($borderStyle);
                $currentRow++;
            }
        }

        $totalRow1Nubox = $currentRow;
        $sheet->setCellValue("C$currentRow", "TOTAL");
        $sheet->setCellValue("D$currentRow", $totalNubox);
        $sheet->setCellValue("E$currentRow", $totalNotasNubox);
        $sheet->getStyle("D$currentRow:E$currentRow")->applyFromArray($greenTotalStyle);
        $currentRow++;
        $totalRow2Nubox = $currentRow;
        $totalNetoNubox = $totalNubox + $totalNotasNubox;
        $sheet->setCellValue("D$currentRow", $totalNetoNubox);
        $sheet->mergeCells("D$currentRow:E$currentRow");
        $sheet->getStyle("D$currentRow:E$currentRow")->applyFromArray(array_merge($greenTotalStyle, [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]));

        $sheet->mergeCells("C$totalRow1Nubox:C$totalRow2Nubox");
        $sheet->getStyle("C$totalRow1Nubox:C$totalRow2Nubox")->applyFromArray(array_merge($greenTotalStyle, [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]));

        $maxRowNubox = $currentRow;

        // TABLA EDSUITE (G-J)
        $currentRow = $startRow;
        $sheet->setCellValue("G$currentRow", "EDSUITE");
        $sheet->mergeCells("G$currentRow:J$currentRow");
        $sheet->getStyle("G$currentRow:J$currentRow")->applyFromArray($yellowHeaderStyle);

        $sheet->setCellValue("G" . ($currentRow + 1), "SERIES");
        $sheet->setCellValue("H" . ($currentRow + 1), "TOTAL");
        $sheet->setCellValue("I" . ($currentRow + 1), "NOTA DE CREDITO");
        $sheet->setCellValue("J" . ($currentRow + 1), "DIFERENCIA");
        $sheet->getStyle("G" . ($currentRow + 1) . ":J" . ($currentRow + 1))->applyFromArray($blueHeaderStyle);

        $currentRow += 2;
        $totalEdsuite = 0;
        $totalNotasEdsuite = 0;
        $totalDiferenciaEdsuite = 0;

        if (!empty($cuadresEDSUITE)) {
            foreach ($cuadresEDSUITE as $cuadre) {
                $serie = $cuadre['serie'];
                $sheet->setCellValue("G$currentRow", $serie);

                if ($cuadre['tipo_comprobante'] == 3) {
                    $sheet->setCellValue("H$currentRow", 0);
                    $sheet->setCellValue("I$currentRow", $cuadre['monto_total']);
                    $totalNotasEdsuite += $cuadre['monto_total'];
                    $nuboxNotas = isset($nuboxPorSerie[$serie]) ? abs($nuboxPorSerie[$serie]['notas']) : 0;
                    $edsuiteNotas = abs($cuadre['monto_total']);
                    $diferencia = $edsuiteNotas - $nuboxNotas;
                } else {
                    $sheet->setCellValue("H$currentRow", $cuadre['monto_total']);
                    $sheet->setCellValue("I$currentRow", 0);
                    $totalEdsuite += $cuadre['monto_total'];
                    $nuboxTotal = isset($nuboxPorSerie[$serie]) ? $nuboxPorSerie[$serie]['total'] : 0;
                    $diferencia = $cuadre['monto_total'] - $nuboxTotal;
                }

                $sheet->setCellValue("J$currentRow", $diferencia);
                $totalDiferenciaEdsuite += $diferencia;

                if ($diferencia != 0) {
                    $sheet->getStyle("J$currentRow")->applyFromArray($redStyle);
                    $sheet->getStyle("G$currentRow:I$currentRow")->applyFromArray($borderStyle);
                } else {
                    $sheet->getStyle("G$currentRow:J$currentRow")->applyFromArray($borderStyle);
                }
                $currentRow++;
            }
        }

        $totalRow1Edsuite = $currentRow;
        $sheet->setCellValue("G$currentRow", "TOTAL");
        $sheet->setCellValue("H$currentRow", $totalEdsuite);
        $sheet->setCellValue("I$currentRow", $totalNotasEdsuite);
        $sheet->setCellValue("J$currentRow", $totalDiferenciaEdsuite);
        $sheet->getStyle("H$currentRow:I$currentRow")->applyFromArray($greenTotalStyle);

        if ($totalDiferenciaEdsuite != 0) {
            $sheet->getStyle("J$currentRow")->applyFromArray($redStyle);
        } else {
            $sheet->getStyle("J$currentRow")->applyFromArray($greenTotalStyle);
        }
        $currentRow++;

        $totalRow2Edsuite = $currentRow;
        $totalNetoEdsuite = $totalEdsuite + $totalNotasEdsuite;
        $totalNetoNuboxCompleto = $totalNubox + $totalNotasNubox;
        $diferenciaTotalNetoEdsuite = $totalNetoEdsuite - $totalNetoNuboxCompleto;
        $sheet->setCellValue("H$currentRow", $totalNetoEdsuite);
        $sheet->setCellValue("J$currentRow", $diferenciaTotalNetoEdsuite);
        $sheet->mergeCells("H$currentRow:I$currentRow");
        $sheet->getStyle("H$currentRow:I$currentRow")->applyFromArray(array_merge($greenTotalStyle, [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]));

        if ($diferenciaTotalNetoEdsuite != 0) {
            $sheet->getStyle("J$currentRow")->applyFromArray($redStyle);
        } else {
            $sheet->getStyle("J$currentRow")->applyFromArray($greenTotalStyle);
        }

        $sheet->mergeCells("G$totalRow1Edsuite:G$totalRow2Edsuite");
        $sheet->getStyle("G$totalRow1Edsuite:G$totalRow2Edsuite")->applyFromArray(array_merge($greenTotalStyle, [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]));

        $sheet->mergeCells("J$totalRow1Edsuite:J$totalRow2Edsuite");
        $sheet->setCellValue("J$totalRow1Edsuite", $totalDiferenciaEdsuite);

        if ($totalDiferenciaEdsuite != 0) {
            $sheet->getStyle("J$totalRow1Edsuite:J$totalRow2Edsuite")->applyFromArray(array_merge($redStyle, [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ]));
        } else {
            $sheet->getStyle("J$totalRow1Edsuite:J$totalRow2Edsuite")->applyFromArray(array_merge($greenTotalStyle, [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ]));
        }

        $maxRowEdsuite = $currentRow;

        // TABLA SIRE (L-O)
        $currentRow = $startRow;
        $sheet->setCellValue("L$currentRow", "SIRE");
        $sheet->mergeCells("L$currentRow:O$currentRow");
        $sheet->getStyle("L$currentRow:O$currentRow")->applyFromArray($yellowHeaderStyle);

        $sheet->setCellValue("L" . ($currentRow + 1), "SERIES");
        $sheet->setCellValue("M" . ($currentRow + 1), "TOTAL");
        $sheet->setCellValue("N" . ($currentRow + 1), "NOTA DE CREDITO");
        $sheet->setCellValue("O" . ($currentRow + 1), "DIFERENCIA");
        $sheet->getStyle("L" . ($currentRow + 1) . ":O" . ($currentRow + 1))->applyFromArray($blueHeaderStyle);

        $currentRow += 2;
        $totalSire = 0;
        $totalNotasSire = 0;
        $totalDiferenciaSire = 0;

        if (!empty($cuadresSIRE)) {
            foreach ($cuadresSIRE as $cuadre) {
                $serie = $cuadre['serie'];
                $sheet->setCellValue("L$currentRow", $serie);

                if ($cuadre['tipo_comprobante'] == 3) {
                    $sheet->setCellValue("M$currentRow", 0);
                    $sheet->setCellValue("N$currentRow", $cuadre['monto_total']);
                    $totalNotasSire += $cuadre['monto_total'];
                    $sireNotas = abs($cuadre['monto_total']);
                    $nuboxNotas = isset($nuboxPorSerie[$serie]) ? abs($nuboxPorSerie[$serie]['notas']) : 0;
                    $diferencia = $sireNotas - $nuboxNotas;
                } else {
                    $sheet->setCellValue("M$currentRow", $cuadre['monto_total']);
                    $sheet->setCellValue("N$currentRow", 0);
                    $totalSire += $cuadre['monto_total'];
                    $nuboxTotal = isset($nuboxPorSerie[$serie]) ? $nuboxPorSerie[$serie]['total'] : 0;
                    $diferencia = $cuadre['monto_total'] - $nuboxTotal;
                }

                $sheet->setCellValue("O$currentRow", $diferencia);
                $totalDiferenciaSire += $diferencia;
                if ($diferencia != 0) {
                    $sheet->getStyle("O$currentRow")->applyFromArray($redStyle);
                    $sheet->getStyle("L$currentRow:N$currentRow")->applyFromArray($borderStyle);
                } else {
                    $sheet->getStyle("L$currentRow:O$currentRow")->applyFromArray($borderStyle);
                }
                $currentRow++;
            }
        }

        $totalRow1Sire = $currentRow;
        $sheet->setCellValue("L$currentRow", "TOTAL");
        $sheet->setCellValue("M$currentRow", $totalSire);
        $sheet->setCellValue("N$currentRow", $totalNotasSire);
        $sheet->setCellValue("O$currentRow", $totalDiferenciaSire);
        $sheet->getStyle("M$currentRow:N$currentRow")->applyFromArray($greenTotalStyle);

        if ($totalDiferenciaSire != 0) {
            $sheet->getStyle("O$currentRow")->applyFromArray($redStyle);
        } else {
            $sheet->getStyle("O$currentRow")->applyFromArray($greenTotalStyle);
        }
        $currentRow++;

        $totalRow2Sire = $currentRow;
        $totalNetoSire = $totalSire + $totalNotasSire;
        $totalNetoNuboxCompleto = $totalNubox + $totalNotasNubox;
        $diferenciaTotalNetoSire = $totalNetoSire - $totalNetoNuboxCompleto;

        $sheet->setCellValue("M$currentRow", $totalNetoSire);
        $sheet->setCellValue("O$currentRow", $diferenciaTotalNetoSire);

        $sheet->mergeCells("M$currentRow:N$currentRow");
        $sheet->getStyle("M$currentRow:N$currentRow")->applyFromArray(array_merge($greenTotalStyle, [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]));

        if ($diferenciaTotalNetoSire != 0) {
            $sheet->getStyle("O$currentRow")->applyFromArray($redStyle);
        } else {
            $sheet->getStyle("O$currentRow")->applyFromArray($greenTotalStyle);
        }

        $sheet->mergeCells("L$totalRow1Sire:L$totalRow2Sire");
        $sheet->getStyle("L$totalRow1Sire:L$totalRow2Sire")->applyFromArray(array_merge($greenTotalStyle, [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]));

        $sheet->mergeCells("O$totalRow1Sire:O$totalRow2Sire");
        $sheet->setCellValue("O$totalRow1Sire", $totalDiferenciaSire);

        if ($totalDiferenciaSire != 0) {
            $sheet->getStyle("O$totalRow1Sire:O$totalRow2Sire")->applyFromArray(array_merge($redStyle, [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ]));
        } else {
            $sheet->getStyle("O$totalRow1Sire:O$totalRow2Sire")->applyFromArray(array_merge($greenTotalStyle, [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ]));
        }

        $maxRowSire = $currentRow;

        $row = max($maxRowNubox, $maxRowEdsuite, $maxRowSire) + 1;
    }

    private function SeccionResumenComprobante($sheet, &$row, $totalesTipoDoc, $yellowHeaderStyle, $greenTotalStyle, $redStyle, $borderStyle)
    {
        $startRow = $row;

        // FACTURAS (C-D)
        $sheet->setCellValue("C$startRow", "FACTURAS");
        $sheet->mergeCells("C$startRow:D$startRow");
        $sheet->getStyle("C$startRow:D$startRow")->applyFromArray($yellowHeaderStyle);

        $sheet->setCellValue("C" . ($startRow + 1), "SIRE");
        $sheet->setCellValue("D" . ($startRow + 1), isset($totalesTipoDoc[2][2]) ? $totalesTipoDoc[2][2] : 0);
        $sheet->getStyle("C" . ($startRow + 1) . ":D" . ($startRow + 1))->applyFromArray($borderStyle);

        $sheet->setCellValue("C" . ($startRow + 2), "NUBOX");
        $sheet->setCellValue("D" . ($startRow + 2), isset($totalesTipoDoc[2][1]) ? $totalesTipoDoc[2][1] : 0);
        $sheet->getStyle("C" . ($startRow + 2) . ":D" . ($startRow + 2))->applyFromArray($borderStyle);

        $faltanteFact = (isset($totalesTipoDoc[2][2]) ? $totalesTipoDoc[2][2] : 0) - (isset($totalesTipoDoc[2][1]) ? $totalesTipoDoc[2][1] : 0);
        $sheet->setCellValue("C" . ($startRow + 3), "FALTANTE");
        $sheet->setCellValue("D" . ($startRow + 3), $faltanteFact);
        if ($faltanteFact == 0) {
            $sheet->getStyle("C" . ($startRow + 3) . ":D" . ($startRow + 3))->applyFromArray($greenTotalStyle);
        } else {
            $sheet->getStyle("C" . ($startRow + 3) . ":D" . ($startRow + 3))->applyFromArray($redStyle);
        }

        // BOLETAS (G-H)
        $sheet->setCellValue("G$startRow", "BOLETAS");
        $sheet->mergeCells("G$startRow:H$startRow");
        $sheet->getStyle("G$startRow:H$startRow")->applyFromArray($yellowHeaderStyle);

        $sheet->setCellValue("G" . ($startRow + 1), "SIRE");
        $sheet->setCellValue("H" . ($startRow + 1), isset($totalesTipoDoc[1][2]) ? $totalesTipoDoc[1][2] : 0);
        $sheet->getStyle("G" . ($startRow + 1) . ":H" . ($startRow + 1))->applyFromArray($borderStyle);

        $sheet->setCellValue("G" . ($startRow + 2), "NUBOX");
        $sheet->setCellValue("H" . ($startRow + 2), isset($totalesTipoDoc[1][1]) ? $totalesTipoDoc[1][1] : 0);
        $sheet->getStyle("G" . ($startRow + 2) . ":H" . ($startRow + 2))->applyFromArray($borderStyle);

        $faltanteBoleta = (isset($totalesTipoDoc[1][2]) ? $totalesTipoDoc[1][2] : 0) - (isset($totalesTipoDoc[1][1]) ? $totalesTipoDoc[1][1] : 0);
        $sheet->setCellValue("G" . ($startRow + 3), "FALTANTE");
        $sheet->setCellValue("H" . ($startRow + 3), $faltanteBoleta);
        if ($faltanteBoleta == 0) {
            $sheet->getStyle("G" . ($startRow + 3) . ":H" . ($startRow + 3))->applyFromArray($greenTotalStyle);
        } else {
            $sheet->getStyle("G" . ($startRow + 3) . ":H" . ($startRow + 3))->applyFromArray($redStyle);
        }

        // NOTAS DE CRÉDITO (K-L)
        $sheet->setCellValue("K$startRow", "NOTAS DE CRÉDITO");
        $sheet->mergeCells("K$startRow:L$startRow");
        $sheet->getStyle("K$startRow:L$startRow")->applyFromArray($yellowHeaderStyle);

        $sheet->setCellValue("K" . ($startRow + 1), "SIRE");
        $sheet->setCellValue("L" . ($startRow + 1), isset($totalesTipoDoc[3][2]) ? $totalesTipoDoc[3][2] : 0);
        $sheet->getStyle("K" . ($startRow + 1) . ":L" . ($startRow + 1))->applyFromArray($borderStyle);

        $sheet->setCellValue("K" . ($startRow + 2), "NUBOX");
        $sheet->setCellValue("L" . ($startRow + 2), isset($totalesTipoDoc[3][1]) ? $totalesTipoDoc[3][1] : 0);
        $sheet->getStyle("K" . ($startRow + 2) . ":L" . ($startRow + 2))->applyFromArray($borderStyle);

        $faltanteNota = (isset($totalesTipoDoc[3][2]) ? $totalesTipoDoc[3][2] : 0) - (isset($totalesTipoDoc[3][1]) ? $totalesTipoDoc[3][1] : 0);
        $sheet->setCellValue("K" . ($startRow + 3), "FALTANTE");
        $sheet->setCellValue("L" . ($startRow + 3), $faltanteNota);
        if ($faltanteNota == 0) {
            $sheet->getStyle("K" . ($startRow + 3) . ":L" . ($startRow + 3))->applyFromArray($greenTotalStyle);
        } else {
            $sheet->getStyle("K" . ($startRow + 3) . ":L" . ($startRow + 3))->applyFromArray($redStyle);
        }

        $row = $startRow + 4;
    }

    private function SeccionReportesGlobales($sheet, &$row, $cuadresNUBOX, $seriesAjenas, $ventasGlobales, $mesSeleccionado, $seriesEdSuite, $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle)
    {
        $startRow = $row;

        // --- TABLA REPORTES GLOBALES EDSuite (LADO IZQUIERDO C-G) ---
        $sheet->setCellValue("C$startRow", "REPORTES GLOBALES");
        $sheet->mergeCells("C$startRow:G$startRow");
        $sheet->getStyle("C$startRow:G$startRow")->applyFromArray($yellowHeaderStyle);

        $sheet->setCellValue("C" . ($startRow + 1), "SERIES");
        $sheet->setCellValue("D" . ($startRow + 1), "COMBUSTIBLES");
        $sheet->setCellValue("E" . ($startRow + 1), "EXTRAS");
        $sheet->setCellValue("F" . ($startRow + 1), "NOTAS DE CREDITO");
        $sheet->setCellValue("G" . ($startRow + 1), "DIFERENCIA");
        $sheet->getStyle("C" . ($startRow + 1) . ":G" . ($startRow + 1))->applyFromArray($blueHeaderStyle);

        $currentRowReportes = $startRow + 2;
        $totalCombustibles = 0;
        $totalExtras = 0;
        $totalNotasCredito = 0;
        $totalDiferencias = 0;
        $reportesEDSuite = [];
        try {
            $todosReportes = Cuadre::obtenerResumenComprobantes($mesSeleccionado);
            // Filtrar solo las series EDSUITE
            $soloSeries = array_column($seriesEdSuite, 'serie');
            $seriesEdSuiteLookup = array_flip($soloSeries);
            foreach ($todosReportes as $reporte) {
                $serie = '';
                if (isset($reporte['serie'])) {
                    if (is_array($reporte['serie'])) {
                        $serie = isset($reporte['serie']['serie']) ? $reporte['serie']['serie'] : '';
                    } else {
                        $serie = (string)$reporte['serie'];
                    }
                }
                if ($serie !== '' && isset($seriesEdSuiteLookup[$serie])) {
                    $reportesEDSuite[] = $reporte;
                }
            }
        } catch (Exception $e) {
            error_log("Error al obtener reportes EDSuite: " . $e->getMessage());
            $reportesEDSuite = [];
        }

        $nuboxPorSerie = [];
        foreach ($cuadresNUBOX as $cuadre) {
            $serie = $cuadre['serie'];
            if (!isset($nuboxPorSerie[$serie])) {
                $nuboxPorSerie[$serie] = 0;
            }
            if ($cuadre['tipo_comprobante'] != 3) {
                $nuboxPorSerie[$serie] += $cuadre['monto_total'];
            }
        }

        if (!empty($reportesEDSuite)) {
            $reportesPorSerie = [];
            foreach ($reportesEDSuite as $reporte) {
                $serie = $reporte['serie'];
                if (!isset($reportesPorSerie[$serie])) {
                    $reportesPorSerie[$serie] = [
                        'combustibles' => 0
                    ];
                }
                if ($reporte['tipo_comprobante'] != 3) {
                    $reportesPorSerie[$serie]['combustibles'] += $reporte['monto_total'];
                }
            }

            // Solo mostrar las series en $seriesEdSuite (mantener el orden)
            foreach ($seriesEdSuite as $itemSerie) {
                $serieStr = is_array($itemSerie) && isset($itemSerie['serie']) ? $itemSerie['serie'] : (string)$itemSerie;
                if (!isset($reportesPorSerie[$serieStr])) continue;
                $datos = $reportesPorSerie[$serieStr];
                $totalNuboxSerie = isset($nuboxPorSerie[$serieStr]) ? $nuboxPorSerie[$serieStr] : 0;
                $totalEdsuiteSerie = $datos['combustibles'];
                $diferenciaSerie = $totalNuboxSerie - $totalEdsuiteSerie;
                $sheet->setCellValue("C$currentRowReportes", $serieStr);
                $sheet->setCellValue("D$currentRowReportes", $totalEdsuiteSerie);
                $sheet->setCellValue("E$currentRowReportes", 0);
                $sheet->setCellValue("F$currentRowReportes", 0);
                $sheet->setCellValue("G$currentRowReportes", $diferenciaSerie);

                if ($diferenciaSerie != 0) {
                    $sheet->getStyle("G$currentRowReportes")->applyFromArray($redStyle);
                } else {
                    $sheet->getStyle("G$currentRowReportes")->applyFromArray($borderStyle);
                }
                $sheet->getStyle("C$currentRowReportes:F$currentRowReportes")->applyFromArray($borderStyle);
                $totalCombustibles += $totalEdsuiteSerie;
                $totalExtras += 0;
                $totalNotasCredito += 0;
                $totalDiferencias += $diferenciaSerie;
                $currentRowReportes++;
            }
        } else {
            $sheet->setCellValue("C$currentRowReportes", "Sin datos disponibles");
            $sheet->mergeCells("C$currentRowReportes:G$currentRowReportes");
            $sheet->getStyle("C$currentRowReportes:G$currentRowReportes")->applyFromArray($borderStyle);
            $currentRowReportes++;
        }

        // Total Reportes EDSuite - Primera fila (D, E, F, G)
        $sheet->setCellValue("C$currentRowReportes", "TOTAL");
        $sheet->setCellValue("D$currentRowReportes", $totalCombustibles);
        $sheet->setCellValue("E$currentRowReportes", $totalExtras);
        $sheet->setCellValue("F$currentRowReportes", $totalNotasCredito);
        $sheet->setCellValue("G$currentRowReportes", $totalDiferencias);
        $sheet->getStyle("D$currentRowReportes:F$currentRowReportes")->applyFromArray($greenTotalStyle);

        if ($totalDiferencias != 0) {
            $sheet->getStyle("G$currentRowReportes")->applyFromArray($redStyle);
        } else {
            $sheet->getStyle("G$currentRowReportes")->applyFromArray($greenTotalStyle);
        }

        $totalRow1Reportes = $currentRowReportes;
        $currentRowReportes++;

        // Total Reportes EDSuite - Segunda fila (D+E+F combinadas, G)
        $totalNetoReportes = $totalCombustibles + $totalExtras + $totalNotasCredito;
        $sheet->setCellValue("D$currentRowReportes", $totalNetoReportes);
        $sheet->setCellValue("G$currentRowReportes", $totalDiferencias);
        $sheet->mergeCells("D$currentRowReportes:F$currentRowReportes");
        $sheet->getStyle("D$currentRowReportes:F$currentRowReportes")->applyFromArray(array_merge($greenTotalStyle, [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]));

        $sheet->mergeCells("C$totalRow1Reportes:C$currentRowReportes");
        $sheet->getStyle("C$totalRow1Reportes:C$currentRowReportes")->applyFromArray(array_merge($greenTotalStyle, [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]));

        $sheet->mergeCells("G$totalRow1Reportes:G$currentRowReportes");
        if ($totalDiferencias != 0) {
            $sheet->getStyle("G$totalRow1Reportes:G$currentRowReportes")->applyFromArray(array_merge($redStyle, [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ]));
        } else {
            $sheet->getStyle("G$totalRow1Reportes:G$currentRowReportes")->applyFromArray(array_merge($greenTotalStyle, [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ]));
        }

        $maxRowReportes = $currentRowReportes;

        // --- TABLA VENTAS GLOBALES (CENTRO I-K) ---
        $currentRowVentasGlobales = $startRow;
        $sheet->setCellValue("I$currentRowVentasGlobales", "PRODUCTOS TOTALES");
        $sheet->mergeCells("I$currentRowVentasGlobales:K$currentRowVentasGlobales");
        $sheet->getStyle("I$currentRowVentasGlobales:K$currentRowVentasGlobales")->applyFromArray($yellowHeaderStyle);

        $sheet->setCellValue("I" . ($currentRowVentasGlobales + 1), "PRODUCTO");
        $sheet->setCellValue("J" . ($currentRowVentasGlobales + 1), "CANTIDAD");
        $sheet->setCellValue("K" . ($currentRowVentasGlobales + 1), "MONTO");
        $sheet->getStyle("I" . ($currentRowVentasGlobales + 1) . ":K" . ($currentRowVentasGlobales + 1))->applyFromArray($blueHeaderStyle);

        $currentRowVentas = $currentRowVentasGlobales + 2;
        $totalCantidadGlobal = 0;
        $totalMontoGlobal = 0;

        if (!empty($ventasGlobales)) {
            foreach ($ventasGlobales as $venta) {
                $sheet->setCellValue("I$currentRowVentas", $venta['producto']);
                $sheet->setCellValue("J$currentRowVentas", $venta['total_cantidad']);
                $sheet->setCellValue("K$currentRowVentas", $venta['total_importe']);
                $sheet->getStyle("I$currentRowVentas:K$currentRowVentas")->applyFromArray($borderStyle);
                $totalCantidadGlobal += $venta['total_cantidad'];
                $totalMontoGlobal += $venta['total_importe'];
                $currentRowVentas++;
            }
        } else {
            $sheet->setCellValue("I$currentRowVentas", "Sin datos disponibles");
            $sheet->mergeCells("I$currentRowVentas:K$currentRowVentas");
            $sheet->getStyle("I$currentRowVentas:K$currentRowVentas")->applyFromArray($borderStyle);
            $currentRowVentas++;
        }

        // Total Ventas Globales
        $sheet->setCellValue("I$currentRowVentas", "TOTAL");
        $sheet->setCellValue("J$currentRowVentas", $totalCantidadGlobal);
        $sheet->setCellValue("K$currentRowVentas", $totalMontoGlobal);
        $sheet->getStyle("I$currentRowVentas:K$currentRowVentas")->applyFromArray($greenTotalStyle);

        // --- TABLA SERIES AJENAS (DERECHA M-O) ---
        $currentRowAjenas = $startRow;
        $sheet->setCellValue("M$currentRowAjenas", "SERIES AJENAS");
        $sheet->mergeCells("M$currentRowAjenas:O$currentRowAjenas");
        $sheet->getStyle("M$currentRowAjenas:O$currentRowAjenas")->applyFromArray($yellowHeaderStyle);

        $sheet->setCellValue("M" . ($currentRowAjenas + 1), "SERIE");
        $sheet->setCellValue("N" . ($currentRowAjenas + 1), "CANTIDAD");
        $sheet->setCellValue("O" . ($currentRowAjenas + 1), "MONTO");
        $sheet->getStyle("M" . ($currentRowAjenas + 1) . ":O" . ($currentRowAjenas + 1))->applyFromArray($blueHeaderStyle);

        $currentRowAjenas += 2;
        $totalConteoAjenas = 0;
        $totalImporteAjenas = 0;

        if (!empty($seriesAjenas)) {
            foreach ($seriesAjenas as $serie) {
                $sheet->setCellValue("M$currentRowAjenas", $serie['serie']);
                $sheet->setCellValue("N$currentRowAjenas", $serie['total_conteo']);
                $sheet->setCellValue("O$currentRowAjenas", $serie['total_importe']);
                $sheet->getStyle("M$currentRowAjenas:O$currentRowAjenas")->applyFromArray($borderStyle);
                $totalConteoAjenas += $serie['total_conteo'];
                $totalImporteAjenas += $serie['total_importe'];
                $currentRowAjenas++;
            }
        } else {
            $sheet->setCellValue("M$currentRowAjenas", "Sin datos disponibles");
            $sheet->mergeCells("M$currentRowAjenas:O$currentRowAjenas");
            $sheet->getStyle("M$currentRowAjenas:O$currentRowAjenas")->applyFromArray($borderStyle);
            $currentRowAjenas++;
        }

        // Total Series Ajenas
        $sheet->setCellValue("M$currentRowAjenas", "TOTAL");
        $sheet->setCellValue("N$currentRowAjenas", $totalConteoAjenas);
        $sheet->setCellValue("O$currentRowAjenas", $totalImporteAjenas);
        $sheet->getStyle("M$currentRowAjenas:O$currentRowAjenas")->applyFromArray($greenTotalStyle);

        $row = max($maxRowReportes, $currentRowVentas, $currentRowAjenas) + 1;
    }

    private function SeccionCabecera($sheet, $nombreMes, $nombreEstablecimiento, $rucEstablecimiento, $usuarioNombre, $headerPrincipalStyle, $headerSecundarioStyle)
    {
        $sheet->setCellValue('A1', '📊 ESCIENZA - REPORTE MENSUAL DE CUADRES 📊');
        $sheet->mergeCells('A1:R1');
        $sheet->getStyle('A1:R1')->applyFromArray($headerPrincipalStyle);
        $sheet->getRowDimension('1')->setRowHeight(30);
        $empresaInfo = "🏢 RUC: $rucEstablecimiento | $nombreEstablecimiento";
        $periodoInfo = "📅 Período: $nombreMes";
        $sheet->setCellValue('A2', $empresaInfo);
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('L2', $periodoInfo);
        $sheet->mergeCells('L2:R2');
        $sheet->getStyle('A2:R2')->applyFromArray($headerSecundarioStyle);
        $sheet->getRowDimension('2')->setRowHeight(22);
        $usuarioInfo = "👤 Generado por: $usuarioNombre";
        $fechaInfo = "🕒 Fecha de generación: " . date('d/m/Y H:i:s');

        $sheet->setCellValue('A3', $usuarioInfo);
        $sheet->mergeCells('A3:K3');
        $sheet->setCellValue('L3', $fechaInfo);
        $sheet->mergeCells('L3:R3');
        $sheet->getStyle('A3:R3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '374151']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            'alignment' => ['horizontal' => 'left', 'vertical' => 'center']
        ]);
        $sheet->getRowDimension('3')->setRowHeight(20);
        $sheet->setCellValue('A4', '');
        $sheet->mergeCells('A4:R4');
        $sheet->getStyle('A4:R4')->applyFromArray([
            'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK, 'color' => ['rgb' => '2563EB']]]
        ]);
        $sheet->getRowDimension('4')->setRowHeight(8);
    }

    private function SeccionSeparador($sheet, &$row, $titulo, $separadorStyle)
    {
        $row++;
        $textoSeparador = "▌ $titulo ▌";
        $sheet->setCellValue("A$row", $textoSeparador);
        $sheet->mergeCells("A$row:R$row");
        $sheet->getStyle("A$row:R$row")->applyFromArray($separadorStyle);
        $sheet->getRowDimension($row)->setRowHeight(28);
        $sheet->getStyle("A$row")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setIndent(1);
        $row++;
    }
}
