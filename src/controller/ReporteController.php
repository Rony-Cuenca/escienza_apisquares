<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../model/Cuadre.php';
require_once __DIR__ . '/../model/Establecimiento.php';
require_once __DIR__ . '/../model/SerieAjena.php';
require_once __DIR__ . '/../model/VentaGlobal.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteController
{
    public function index()
    {
        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();
        $mesSeleccionado = $_GET['mes'] ?? '';
        $cuadresSIRE = $cuadresNUBOX = $cuadresEDSUITE = [];
        $totalesTipoDoc = $seriesTotales = $diferenciasSeries = [];
        $seriesAjenas = $ventasGlobales = [];

        if ($mesSeleccionado) {
            $cuadres = Cuadre::obtenerCuadresPorMes($mesSeleccionado);
            list($cuadresSIRE, $cuadresNUBOX, $cuadresEDSUITE) = $this->separarCuadresPorSistemaExcluyendoAjenas($cuadres, $mesSeleccionado);

            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoComprobanteExcluyendoAjenas($mesSeleccionado);
            $seriesTotales = Cuadre::obtenerTotalesPorSerieExcluyendoAjenas($mesSeleccionado);
            $diferenciasSeries = $this->calcularDiferenciasSeries($seriesTotales);
            $seriesAjenas = SerieAjena::obtenerPorMes($mesSeleccionado);
            $ventasGlobales = VentaGlobal::obtenerPorMes($mesSeleccionado);
        }

        $contenido = 'view/components/reporte.php';
        require 'view/layout.php';
    }

    private function separarCuadresPorSistema($cuadres)
    {
        $cuadresSIRE = $cuadresNUBOX = $cuadresEDSUITE = [];
        foreach ($cuadres as $cuadre) {
            if ($cuadre['id_reporte'] == 2) {
                $cuadresSIRE[] = $cuadre;
            } elseif ($cuadre['id_reporte'] == 1) {
                $cuadresNUBOX[] = $cuadre;
            } elseif ($cuadre['id_reporte'] == 3) {
                $cuadresEDSUITE[] = $cuadre;
            }
        }
    }

    private function separarCuadresPorSistemaExcluyendoAjenas($cuadres, $mesSeleccionado)
    {
        $seriesAjenasArray = SerieAjena::obtenerPorMes($mesSeleccionado);
        $seriesAjenasLista = array_column($seriesAjenasArray, 'serie');

        $cuadresSIRE = $cuadresNUBOX = $cuadresEDSUITE = [];
        foreach ($cuadres as $cuadre) {
            if (in_array($cuadre['serie'], $seriesAjenasLista)) {
                continue;
            }

            if ($cuadre['id_reporte'] == 2) {
                $cuadresSIRE[] = $cuadre;
            } elseif ($cuadre['id_reporte'] == 1) {
                $cuadresNUBOX[] = $cuadre;
            } elseif ($cuadre['id_reporte'] == 3) {
                $cuadresEDSUITE[] = $cuadre;
            }
        }
        return [$cuadresSIRE, $cuadresNUBOX, $cuadresEDSUITE];
    }

    private function calcularDiferenciasSeries($seriesTotales)
    {
        $diferenciasSeries = [];
        $todasSeries = array_unique(array_keys($seriesTotales));
        foreach ($todasSeries as $serie) {
            $totalSIRE = $seriesTotales[$serie][2] ?? 0;
            $totalNUBOX = $seriesTotales[$serie][1] ?? 0;
            $diferenciasSeries[$serie] = [
                'serie' => $serie,
                'total_sire' => $totalSIRE,
                'total_nubox' => $totalNUBOX,
                'diferencia' => $totalSIRE - $totalNUBOX
            ];
        }
        return $diferenciasSeries;
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
        $mesSeleccionado = $_GET['mes'] ?? '';
        $cuadresSIRE = $cuadresNUBOX = $cuadresEDSUITE = [];
        $totalesTipoDoc = $seriesTotales = $diferenciasSeries = [];
        $seriesAjenas = $ventasGlobales = [];
        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();
        $usuarioNombre = $_SESSION['usuario'] ?? 'Desconocido';

        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $rucSucursal = '';
        $nombreSucursal = '';
        if ($id_sucursal) {
            $id_cliente = $_SESSION['id_cliente'] ?? null;
            $sucursal = \Establecimiento::obtenerPorId($id_sucursal, $id_cliente);
            if ($sucursal) {
                $rucSucursal = $sucursal['ruc'] ?? '';
                $nombreSucursal = $sucursal['razon_social'] ?? '';
            }
        }

        if ($mesSeleccionado) {
            $cuadres = Cuadre::obtenerCuadresPorMes($mesSeleccionado);
            list($cuadresSIRE, $cuadresNUBOX, $cuadresEDSUITE) = $this->separarCuadresPorSistemaExcluyendoAjenas($cuadres, $mesSeleccionado);

            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoComprobanteExcluyendoAjenas($mesSeleccionado);
            $seriesTotales = Cuadre::obtenerTotalesPorSerieExcluyendoAjenas($mesSeleccionado);
            $diferenciasSeries = $this->calcularDiferenciasSeries($seriesTotales);
            $seriesAjenas = SerieAjena::obtenerPorMes($mesSeleccionado);
            $ventasGlobales = VentaGlobal::obtenerPorMes($mesSeleccionado);
        }

        $nombreMes = $this->obtenerNombreMes($mesSeleccionado, $mesesDisponibles);

        ob_start();
        include __DIR__ . '/../view/components/reporte_pdf.php';
        $html = ob_get_clean();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->render();
        $fechaActual = date('Y-m-d');
        $dompdf->stream("Reporte de Cuadres - {$nombreMes} - {$fechaActual}.pdf");
        exit;
    }

    public function exportarExcel()
    {
        $mesSeleccionado = $_GET['mes'] ?? '';
        $cuadresSIRE = $cuadresNUBOX = $cuadresEDSUITE = [];
        $totalesTipoDoc = $seriesTotales = $diferenciasSeries = [];
        $seriesAjenas = $ventasGlobales = [];
        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $rucSucursal = '';
        $nombreSucursal = '';
        $usuarioNombre = $_SESSION['usuario'] ?? 'Desconocido';

        if ($id_sucursal) {
            $id_cliente = $_SESSION['id_cliente'] ?? null;
            $sucursal = \Establecimiento::obtenerPorId($id_sucursal, $id_cliente);
            if ($sucursal) {
                $rucSucursal = $sucursal['ruc'] ?? '';
                $nombreSucursal = $sucursal['razon_social'] ?? '';
            }
        }

        if ($mesSeleccionado) {
            $cuadres = Cuadre::obtenerCuadresPorMes($mesSeleccionado);
            list($cuadresSIRE, $cuadresNUBOX, $cuadresEDSUITE) = $this->separarCuadresPorSistemaExcluyendoAjenas($cuadres, $mesSeleccionado);
            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoComprobanteExcluyendoAjenas($mesSeleccionado);
            $seriesTotales = Cuadre::obtenerTotalesPorSerieExcluyendoAjenas($mesSeleccionado);
            $diferenciasSeries = $this->calcularDiferenciasSeries($seriesTotales);
            $seriesAjenas = SerieAjena::obtenerPorMes($mesSeleccionado);
            $ventasGlobales = VentaGlobal::obtenerPorMes($mesSeleccionado);
        }

        $nombreMes = $this->obtenerNombreMes($mesSeleccionado, $mesesDisponibles);
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Cuadres');

        $headerPrincipalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0f172a']], // Slate-900 premium
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1e293b']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $headerSecundarioStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']], // Blue-800 ejecutivo
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1d4ed8']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $separadorStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0f172a']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f8fafc']], // Slate-50 premium
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '2563eb']],
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cbd5e1']]
            ],
            'alignment' => ['horizontal' => 'left', 'vertical' => 'center']
        ];

        $yellowHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '0f172a'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fcd34d']], // Amber-300 ejecutivo
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'f59e0b']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $blueHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']], // Blue-700 corporativo
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1e40af']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $greenTotalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']], // Emerald-600 profesional
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $redStyle = [
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dc2626']], // Red-600 controlado
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'b91c1c']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $borderStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ffffff']] // Fondo blanco limpio
        ];

        $warningStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '0f172a'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fed7aa']], // Orange-200 suave
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'ea580c']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $infoStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0369a1']], // Sky-700 profesional
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0284c7']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $neutralStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '1f2937'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f3f4f6']], // Gray-100 elegante
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9ca3af']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $totalEspecialStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']], // Gray-700 ejecutivo
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1f2937']]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];

        $this->SeccionCabecera($sheet, $nombreMes, $nombreSucursal, $rucSucursal, $usuarioNombre, $headerPrincipalStyle, $headerSecundarioStyle);

        $row = 6;

        $this->SeccionSeparador($sheet, $row, "ANÁLISIS COMPARATIVO POR SISTEMA DE FACTURACIÓN", $separadorStyle);
        $this->SeccionResumenSoftware($sheet, $row, $cuadresNUBOX, $cuadresEDSUITE, $cuadresSIRE, $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle, $warningStyle);

        $row += 2;

        $this->SeccionSeparador($sheet, $row, "RESUMEN DE COMPROBANTES", $separadorStyle);
        $this->SeccionResumenComprobante($sheet, $row, $totalesTipoDoc, $yellowHeaderStyle, $greenTotalStyle, $redStyle, $borderStyle, $infoStyle);

        $row += 2;

        $this->SeccionSeparador($sheet, $row, "REPORTES GLOBALES Y SERIES AJENAS", $separadorStyle);
        $this->SeccionReportesGlobales($sheet, $row, $cuadresNUBOX, $seriesAjenas, $ventasGlobales, $mesSeleccionado, $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle, $warningStyle, $infoStyle);

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(12);

        foreach (range('C', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getColumnDimension($col)->setWidth(max(10, min(25, $sheet->getColumnDimension($col)->getWidth())));
        }

        for ($i = 6; $i <= $row; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(18);
        }

        $sheet->getStyle("C6:R{$row}")->getNumberFormat()->setFormatCode('_("S/" * #,##0.00_);_("S/" * \(#,##0.00\);_("S/" * 0.00_);_(@_)');
        $sheet->getStyle("B6:B{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
        $sheet->getStyle("J6:J{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
        $sheet->getStyle("N6:N{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;0;@');
        $sheet->getStyle("A6:A{$row}")->getAlignment()->setWrapText(true);
        $columnasMomentarias = ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'O', 'P', 'Q', 'R'];
        foreach ($columnasMomentarias as $col) {
            $sheet->getStyle("{$col}6:{$col}{$row}")->getNumberFormat()->setFormatCode('_("S/" * #,##0.00_);_("S/" * \(#,##0.00\);_("S/" * 0.00_);_(@_)');
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

        $nombreArchivo = "Reporte de Cuadres - {$nombreMes} - " . date('Y-m-d') . ".xlsx";
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

    private function SeccionReportesGlobales($sheet, &$row, $cuadresNUBOX, $seriesAjenas, $ventasGlobales, $mesSeleccionado, $yellowHeaderStyle, $blueHeaderStyle, $greenTotalStyle, $borderStyle, $redStyle)
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
            $reportesEDSuite = Cuadre::obtenerResumenComprobantes($mesSeleccionado);
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

            foreach ($reportesPorSerie as $serie => $datos) {
                $totalNuboxSerie = isset($nuboxPorSerie[$serie]) ? $nuboxPorSerie[$serie] : 0;
                $totalEdsuiteSerie = $datos['combustibles'];
                $diferenciaSerie = $totalNuboxSerie - $totalEdsuiteSerie;
                $sheet->setCellValue("C$currentRowReportes", $serie);
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

    private function SeccionCabecera($sheet, $nombreMes, $nombreSucursal, $rucSucursal, $usuarioNombre, $headerPrincipalStyle, $headerSecundarioStyle)
    {
        $sheet->setCellValue('A1', '📊 ESCIENZA - REPORTE MENSUAL DE CUADRES 📊');
        $sheet->mergeCells('A1:R1');
        $sheet->getStyle('A1:R1')->applyFromArray($headerPrincipalStyle);
        $sheet->getRowDimension('1')->setRowHeight(30);
        $empresaInfo = "🏢 RUC: $rucSucursal | $nombreSucursal";
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
