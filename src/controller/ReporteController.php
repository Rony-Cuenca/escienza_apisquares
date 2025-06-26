<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../model/Cuadre.php';
require_once __DIR__ . '/../model/Establecimiento.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteController
{
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

    private function escribirTablaSeries($sheet, &$row, $titulo, $cuadres, $headerStyle, $borderStyle, $redStyle, $boldStyle)
    {
        $sheet->setCellValue("A$row", $titulo);
        $sheet->mergeCells("A$row:G$row");
        $sheet->getStyle("A$row")->applyFromArray($boldStyle);
        $row++;

        $headers = ['Serie', 'Cantidad', 'Suma Gravada', 'Suma Exonerada', 'Suma Inafecta', 'Suma IGV', 'Suma Total'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("$col$row", $header);
            $col++;
        }
        $sheet->getStyle("A$row:G$row")->applyFromArray($headerStyle);
        $row++;

        if (!empty($cuadres)) {
            foreach ($cuadres as $cuadre) {
                $col = 'A';
                $sheet->setCellValue("$col$row", $cuadre['serie']);
                $col++;
                $sheet->setCellValue("$col$row", $cuadre['cantidad_compr']);
                $col++;
                $sheet->setCellValue("$col$row", $cuadre['suma_gravada']);
                $col++;
                $sheet->setCellValue("$col$row", $cuadre['suma_exonerada']);
                $col++;
                $sheet->setCellValue("$col$row", $cuadre['suma_inafecto']);
                $col++;
                $sheet->setCellValue("$col$row", $cuadre['suma_igv']);
                $col++;
                $sheet->setCellValue("$col$row", $cuadre['monto_total']);
                $sheet->getStyle("A$row:G$row")->applyFromArray($borderStyle);
                if ($cuadre['monto_total'] < 0) {
                    $sheet->getStyle("A$row:G$row")->applyFromArray($redStyle);
                }
                $row++;
            }
        } else {
            $sheet->setCellValue("A$row", "No hay cuadres para este mes.");
            $sheet->mergeCells("A$row:G$row");
            $row++;
        }
        $row++;
    }

    public function index()
    {
        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();
        $mesSeleccionado = $_GET['mes'] ?? '';
        $cuadresSIRE = $cuadresNUBOX = $cuadresEDSUITE = [];
        $totalesTipoDoc = $seriesTotales = $diferenciasSeries = [];

        if ($mesSeleccionado) {
            $cuadres = Cuadre::obtenerCuadresPorMes($mesSeleccionado);
            list($cuadresSIRE, $cuadresNUBOX, $cuadresEDSUITE) = $this->separarCuadresPorSistema($cuadres);

            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoComprobante($mesSeleccionado);
            $seriesTotales = Cuadre::obtenerTotalesPorSerie($mesSeleccionado);
            $diferenciasSeries = $this->calcularDiferenciasSeries($seriesTotales);
        }

        $contenido = 'view/components/reporte.php';
        require 'view/layout.php';
    }

    public function exportarPDF()
    {
        $mesSeleccionado = $_GET['mes'] ?? '';
        $cuadresSIRE = $cuadresNUBOX = $cuadresEDSUITE = [];
        $totalesTipoDoc = $seriesTotales = $diferenciasSeries = [];
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
            list($cuadresSIRE, $cuadresNUBOX, $cuadresEDSUITE) = $this->separarCuadresPorSistema($cuadres);

            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoComprobante($mesSeleccionado);
            $seriesTotales = Cuadre::obtenerTotalesPorSerie($mesSeleccionado);
            $diferenciasSeries = $this->calcularDiferenciasSeries($seriesTotales);
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
        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();

        if ($mesSeleccionado) {
            $cuadres = Cuadre::obtenerCuadresPorMes($mesSeleccionado);
            list($cuadresSIRE, $cuadresNUBOX, $cuadresEDSUITE) = $this->separarCuadresPorSistema($cuadres);

            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoComprobante($mesSeleccionado);
            $seriesTotales = Cuadre::obtenerTotalesPorSerie($mesSeleccionado);
            $diferenciasSeries = $this->calcularDiferenciasSeries($seriesTotales);
        }

        $nombreMes = $this->obtenerNombreMes($mesSeleccionado, $mesesDisponibles);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Cuadres');

        // --- ESTILOS ---
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '222222']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A9C3E8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '2563EB']]]
        ];
        $borderStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '2563EB']]]
        ];
        $redStyle = [
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC3545']]
        ];
        $boldStyle = [
            'font' => ['bold' => true]
        ];

        $row = 1;
        $sheet->setCellValue("A$row", "Reporte de Cuadres - $nombreMes");
        $sheet->mergeCells("A$row:G$row");
        $sheet->getStyle("A$row")->applyFromArray(['font' => ['bold' => true, 'size' => 16]]);
        $row += 2;

        // --- TABLAS DE SERIES ---
        $this->escribirTablaSeries($sheet, $row, "Resumen de Series - SIRE", $cuadresSIRE, $headerStyle, $borderStyle, $redStyle, $boldStyle);
        $this->escribirTablaSeries($sheet, $row, "Resumen de Series - NUBOX360", $cuadresNUBOX, $headerStyle, $borderStyle, $redStyle, $boldStyle);
        $this->escribirTablaSeries($sheet, $row, "Resumen de Series - EDSUITE", $cuadresEDSUITE, $headerStyle, $borderStyle, $redStyle, $boldStyle);

        // --- RESÚMENES EN PARALELO ---
        $startRow = $row;

        // FACTURAS (A-B)
        $sheet->setCellValue("A$startRow", "FACTURAS");
        $sheet->mergeCells("A$startRow:B$startRow");
        $sheet->getStyle("A$startRow")->applyFromArray($boldStyle);
        $sheet->setCellValue("A" . ($startRow + 1), "Sistema");
        $sheet->setCellValue("B" . ($startRow + 1), "Monto");
        $sheet->getStyle("A" . ($startRow + 1) . ":B" . ($startRow + 1))->applyFromArray($headerStyle);
        $sheet->setCellValue("A" . ($startRow + 2), "SIRE");
        $sheet->setCellValue("B" . ($startRow + 2), isset($totalesTipoDoc[2][2]) ? $totalesTipoDoc[2][2] : 0);
        $sheet->setCellValue("A" . ($startRow + 3), "NUBOX");
        $sheet->setCellValue("B" . ($startRow + 3), isset($totalesTipoDoc[2][1]) ? $totalesTipoDoc[2][1] : 0);
        $faltanteFact = (isset($totalesTipoDoc[2][2]) ? $totalesTipoDoc[2][2] : 0) - (isset($totalesTipoDoc[2][1]) ? $totalesTipoDoc[2][1] : 0);
        $sheet->setCellValue("A" . ($startRow + 4), "FALTANTE");
        $sheet->setCellValue("B" . ($startRow + 4), $faltanteFact);
        $sheet->getStyle("A" . ($startRow + 4) . ":B" . ($startRow + 4))->applyFromArray($boldStyle);
        if ($faltanteFact != 0) {
            $sheet->getStyle("A" . ($startRow + 4) . ":B" . ($startRow + 4))->applyFromArray($redStyle);
        }

        // BOLETAS (D-E)
        $sheet->setCellValue("D$startRow", "BOLETAS");
        $sheet->mergeCells("D$startRow:E$startRow");
        $sheet->getStyle("D$startRow")->applyFromArray($boldStyle);
        $sheet->setCellValue("D" . ($startRow + 1), "Sistema");
        $sheet->setCellValue("E" . ($startRow + 1), "Monto");
        $sheet->getStyle("D" . ($startRow + 1) . ":E" . ($startRow + 1))->applyFromArray($headerStyle);
        $sheet->setCellValue("D" . ($startRow + 2), "SIRE");
        $sheet->setCellValue("E" . ($startRow + 2), isset($totalesTipoDoc[1][2]) ? $totalesTipoDoc[1][2] : 0);
        $sheet->setCellValue("D" . ($startRow + 3), "NUBOX");
        $sheet->setCellValue("E" . ($startRow + 3), isset($totalesTipoDoc[1][1]) ? $totalesTipoDoc[1][1] : 0);
        $faltanteBoleta = (isset($totalesTipoDoc[1][2]) ? $totalesTipoDoc[1][2] : 0) - (isset($totalesTipoDoc[1][1]) ? $totalesTipoDoc[1][1] : 0);
        $sheet->setCellValue("D" . ($startRow + 4), "FALTANTE");
        $sheet->setCellValue("E" . ($startRow + 4), $faltanteBoleta);
        $sheet->getStyle("D" . ($startRow + 4) . ":E" . ($startRow + 4))->applyFromArray($boldStyle);
        if ($faltanteBoleta != 0) {
            $sheet->getStyle("D" . ($startRow + 4) . ":E" . ($startRow + 4))->applyFromArray($redStyle);
        }

        // NOTAS DE CRÉDITO (G-H)
        $sheet->setCellValue("G$startRow", "NOTAS DE CRÉDITO");
        $sheet->mergeCells("G$startRow:H$startRow");
        $sheet->getStyle("G$startRow")->applyFromArray($boldStyle);
        $sheet->setCellValue("G" . ($startRow + 1), "Sistema");
        $sheet->setCellValue("H" . ($startRow + 1), "Monto");
        $sheet->getStyle("G" . ($startRow + 1) . ":H" . ($startRow + 1))->applyFromArray($headerStyle);
        $sheet->setCellValue("G" . ($startRow + 2), "SIRE");
        $sheet->setCellValue("H" . ($startRow + 2), isset($totalesTipoDoc[3][2]) ? $totalesTipoDoc[3][2] : 0);
        $sheet->setCellValue("G" . ($startRow + 3), "NUBOX");
        $sheet->setCellValue("H" . ($startRow + 3), isset($totalesTipoDoc[3][1]) ? $totalesTipoDoc[3][1] : 0);
        $faltanteNota = (isset($totalesTipoDoc[3][2]) ? $totalesTipoDoc[3][2] : 0) - (isset($totalesTipoDoc[3][1]) ? $totalesTipoDoc[3][1] : 0);
        $sheet->setCellValue("G" . ($startRow + 4), "FALTANTE");
        $sheet->setCellValue("H" . ($startRow + 4), $faltanteNota);
        $sheet->getStyle("G" . ($startRow + 4) . ":H" . ($startRow + 4))->applyFromArray($boldStyle);
        if ($faltanteNota != 0) {
            $sheet->getStyle("G" . ($startRow + 4) . ":H" . ($startRow + 4))->applyFromArray($redStyle);
        }

        $row = $startRow + 6;

        // --- TABLA DIFERENCIAS (SIRE - NUBOX) ---
        $sheet->setCellValue("A$row", "DIFERENCIAS (SIRE - NUBOX)");
        $sheet->mergeCells("A$row:D$row");
        $sheet->getStyle("A$row")->applyFromArray($boldStyle);
        $row++;

        $headers = ['Serie', 'Total SIRE', 'Total NUBOX', 'Diferencia R.G'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("$col$row", $header);
            $col++;
        }
        $sheet->getStyle("A$row:D$row")->applyFromArray($headerStyle);
        $row++;

        $totalSire = 0;
        $totalNubox = 0;
        $totalDif = 0;

        if (!empty($diferenciasSeries)) {
            foreach ($diferenciasSeries as $dif) {
                $sheet->setCellValue("A$row", $dif['serie']);
                $sheet->setCellValue("B$row", $dif['total_sire']);
                $sheet->setCellValue("C$row", $dif['total_nubox']);
                $sheet->setCellValue("D$row", $dif['diferencia']);
                $sheet->getStyle("A$row:D$row")->applyFromArray($borderStyle);
                if ($dif['diferencia'] != 0) {
                    $sheet->getStyle("A$row:D$row")->applyFromArray($redStyle);
                }
                $totalSire += $dif['total_sire'];
                $totalNubox += $dif['total_nubox'];
                $totalDif += $dif['diferencia'];
                $row++;
            }
            $sheet->setCellValue("A$row", "TOTAL");
            $sheet->setCellValue("B$row", $totalSire);
            $sheet->setCellValue("C$row", $totalNubox);
            $sheet->setCellValue("D$row", $totalDif);
            $sheet->getStyle("A$row:D$row")->applyFromArray($boldStyle);
        } else {
            $sheet->setCellValue("A$row", "No hay diferencias para este mes.");
            $sheet->mergeCells("A$row:D$row");
        }
        $row++;

        $sheet->getStyle("B1:B{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("C1:C{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("D1:D{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("E1:E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("F1:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("G1:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("H1:H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fechaActual = date('Y-m-d');
        $nombreArchivo = "Reporte de Cuadres - {$nombreMes} - {$fechaActual}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
