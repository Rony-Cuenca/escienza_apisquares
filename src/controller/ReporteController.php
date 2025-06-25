<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../model/Cuadre.php';
require_once __DIR__ . '/../model/Establecimiento.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteController
{
    public function index()
    {
        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();
        $mesSeleccionado = $_GET['mes'] ?? '';
        $cuadresSIRE = [];
        $cuadresNUBOX = [];
        $cuadresEDSUITE = [];
        $totalesTipoDoc = [];
        $seriesTotales = [];
        $diferenciasSeries = [];

        if ($mesSeleccionado) {
            $cuadres = Cuadre::obtenerCuadresPorMes($mesSeleccionado);
            foreach ($cuadres as $cuadre) {
                if ($cuadre['id_reporte'] == 2) {
                    $cuadresSIRE[] = $cuadre;
                } elseif ($cuadre['id_reporte'] == 1) {
                    $cuadresNUBOX[] = $cuadre;
                } elseif ($cuadre['id_reporte'] == 3) {
                    $cuadresEDSUITE[] = $cuadre;
                }
            }

            // Resumen por tipo de documento (factura/boleta) y sistema
            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoDoc($mesSeleccionado);

            // Totales por serie y sistema
            $seriesTotales = Cuadre::obtenerTotalesPorSerie($mesSeleccionado);

            // Diferencias por serie (solo SIRE y NUBOX)
            $diferenciasSeries = [];
            $todasSeries = array_unique(array_merge(array_keys($seriesTotales)));
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
        }

        $contenido = 'view/components/reporte.php';
        require 'view/layout.php';
    }

    public function exportarPDF()
    {
        $mesSeleccionado = $_GET['mes'] ?? '';
        $cuadresSIRE = [];
        $cuadresNUBOX = [];
        $cuadresEDSUITE = [];
        $totalesTipoDoc = [];
        $seriesTotales = [];
        $diferenciasSeries = [];
        $mesesDisponibles = Cuadre::obtenerMesesDisponibles();
        $usuarioNombre = $_SESSION['usuario'] ?? 'Desconocido';

        // Obtener datos de sucursal/establecimiento
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
            foreach ($cuadres as $cuadre) {
                if ($cuadre['id_reporte'] == 2) {
                    $cuadresSIRE[] = $cuadre;
                } elseif ($cuadre['id_reporte'] == 1) {
                    $cuadresNUBOX[] = $cuadre;
                } elseif ($cuadre['id_reporte'] == 3) {
                    $cuadresEDSUITE[] = $cuadre;
                }
            }

            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoDoc($mesSeleccionado);
            $seriesTotales = Cuadre::obtenerTotalesPorSerie($mesSeleccionado);

            $diferenciasSeries = [];
            $todasSeries = array_unique(array_merge(array_keys($seriesTotales)));
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
        }

        $nombreMes = $mesSeleccionado;
        foreach ($mesesDisponibles as $mes) {
            if ($mes['mes'] == $mesSeleccionado) {
                $nombreMes = $mes['mes_nombre'];
                break;
            }
        }

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
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../model/Cuadre.php';

        $mesSeleccionado = $_GET['mes'] ?? '';
        $cuadresSIRE = [];
        $cuadresNUBOX = [];
        $cuadresEDSUITE = [];
        $totalesTipoDoc = [];
        $seriesTotales = [];
        $diferenciasSeries = [];

        if ($mesSeleccionado) {
            $cuadres = Cuadre::obtenerCuadresPorMes($mesSeleccionado);
            foreach ($cuadres as $cuadre) {
                if ($cuadre['id_reporte'] == 2) {
                    $cuadresSIRE[] = $cuadre;
                } elseif ($cuadre['id_reporte'] == 1) {
                    $cuadresNUBOX[] = $cuadre;
                } elseif ($cuadre['id_reporte'] == 3) {
                    $cuadresEDSUITE[] = $cuadre;
                }
            }
            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoDoc($mesSeleccionado);
            $seriesTotales = Cuadre::obtenerTotalesPorSerie($mesSeleccionado);

            $diferenciasSeries = [];
            $todasSeries = array_unique(array_merge(array_keys($seriesTotales)));
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
        }

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
        $sheet->mergeCells("A$row:H$row");
        $sheet->getStyle("A$row")->applyFromArray(['font' => ['bold' => true, 'size' => 16]]);
        $row += 2;

        // --- TABLA SIRE ---
        $sheet->setCellValue("A$row", "Resumen de Series - SIRE");
        $sheet->mergeCells("A$row:H$row");
        $sheet->getStyle("A$row")->applyFromArray($boldStyle);
        $row++;

        $headers = ['Serie', 'Cantidad', 'Suma Gravada', 'Suma Exonerada', 'Suma Inafecta', 'Suma IGV', 'Suma Total'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("$col$row", $header);
            $col++;
        }
        $sheet->getStyle("A$row:H$row")->applyFromArray($headerStyle);
        $row++;

        if (!empty($cuadresSIRE)) {
            foreach ($cuadresSIRE as $i => $cuadre) {
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
                $sheet->getStyle("A$row:H$row")->applyFromArray($borderStyle);
                if ($cuadre['monto_total'] < 0) {
                    $sheet->getStyle("A$row:H$row")->applyFromArray($redStyle);
                }
                $row++;
            }
        } else {
            $sheet->setCellValue("A$row", "No hay cuadres SIRE para este mes.");
            $sheet->mergeCells("A$row:H$row");
            $row++;
        }
        $row++;

        // --- TABLA NUBOX ---
        $sheet->setCellValue("A$row", "Resumen de Series - NUBOX360");
        $sheet->mergeCells("A$row:H$row");
        $sheet->getStyle("A$row")->applyFromArray($boldStyle);
        $row++;

        $headers = ['Serie', 'Cantidad', 'Suma Gravada', 'Suma Exonerada', 'Suma Inafecta', 'Suma IGV', 'Suma Total'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("$col$row", $header);
            $col++;
        }
        $sheet->getStyle("A$row:H$row")->applyFromArray($headerStyle);
        $row++;

        if (!empty($cuadresNUBOX)) {
            foreach ($cuadresNUBOX as $i => $cuadre) {
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
                $sheet->getStyle("A$row:H$row")->applyFromArray($borderStyle);
                if ($cuadre['monto_total'] < 0) {
                    $sheet->getStyle("A$row:H$row")->applyFromArray($redStyle);
                }
                $row++;
            }
        } else {
            $sheet->setCellValue("A$row", "No hay cuadres NUBOX360 para este mes.");
            $sheet->mergeCells("A$row:H$row");
            $row++;
        }
        $row++;

        // --- TABLA EDSUITE ---
        $sheet->setCellValue("A$row", "Resumen de Series - EDSUITE");
        $sheet->mergeCells("A$row:H$row");
        $sheet->getStyle("A$row")->applyFromArray($boldStyle);
        $row++;

        $headers = ['Serie', 'Cantidad', 'Suma Gravada', 'Suma Exonerada', 'Suma Inafecta', 'Suma IGV', 'Suma Total'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("$col$row", $header);
            $col++;
        }
        $sheet->getStyle("A$row:H$row")->applyFromArray($headerStyle);
        $row++;

        if (!empty($cuadresEDSUITE)) {
            foreach ($cuadresEDSUITE as $i => $cuadre) {
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
                $sheet->getStyle("A$row:H$row")->applyFromArray($borderStyle);
                if ($cuadre['monto_total'] < 0) {
                    $sheet->getStyle("A$row:H$row")->applyFromArray($redStyle);
                }
                $row++;
            }
        } else {
            $sheet->setCellValue("A$row", "No hay cuadres EDSUITE para este mes.");
            $sheet->mergeCells("A$row:H$row");
            $row++;
        }
        $row++;

        // --- RESUMEN FACTURAS ---
        $sheet->setCellValue("A$row", "RESUMEN FACTURAS");
        $sheet->mergeCells("A$row:C$row");
        $sheet->getStyle("A$row")->applyFromArray($boldStyle);
        $row++;

        $headers = ['Sistema', 'Monto'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("$col$row", $header);
            $col++;
        }
        $sheet->getStyle("A$row:B$row")->applyFromArray($headerStyle);
        $row++;

        // SIRE
        $sheet->setCellValue("A$row", "SIRE");
        $sheet->setCellValue("B$row", isset($totalesTipoDoc['FACTURA'][2]) ? $totalesTipoDoc['FACTURA'][2] : 0);
        $sheet->getStyle("A$row:B$row")->applyFromArray($borderStyle);
        $row++;

        // NUBOX
        $sheet->setCellValue("A$row", "NUBOX");
        $sheet->setCellValue("B$row", isset($totalesTipoDoc['FACTURA'][1]) ? $totalesTipoDoc['FACTURA'][1] : 0);
        $sheet->getStyle("A$row:B$row")->applyFromArray($borderStyle);
        $row++;

        // FALTANTE
        $faltanteFact = (isset($totalesTipoDoc['FACTURA'][2]) ? $totalesTipoDoc['FACTURA'][2] : 0) - (isset($totalesTipoDoc['FACTURA'][1]) ? $totalesTipoDoc['FACTURA'][1] : 0);
        $sheet->setCellValue("A$row", "FALTANTE");
        $sheet->setCellValue("B$row", $faltanteFact);
        $sheet->getStyle("A$row:B$row")->applyFromArray($boldStyle);
        if ($faltanteFact != 0) {
            $sheet->getStyle("A$row:B$row")->applyFromArray($redStyle);
        }
        $row += 2;

        // --- RESUMEN BOLETAS ---
        $sheet->setCellValue("A$row", "RESUMEN BOLETAS");
        $sheet->mergeCells("A$row:C$row");
        $sheet->getStyle("A$row")->applyFromArray($boldStyle);
        $row++;

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("$col$row", $header);
            $col++;
        }
        $sheet->getStyle("A$row:B$row")->applyFromArray($headerStyle);
        $row++;

        // SIRE
        $sheet->setCellValue("A$row", "SIRE");
        $sheet->setCellValue("B$row", isset($totalesTipoDoc['BOLETA'][2]) ? $totalesTipoDoc['BOLETA'][2] : 0);
        $sheet->getStyle("A$row:B$row")->applyFromArray($borderStyle);
        $row++;

        // NUBOX
        $sheet->setCellValue("A$row", "NUBOX");
        $sheet->setCellValue("B$row", isset($totalesTipoDoc['BOLETA'][1]) ? $totalesTipoDoc['BOLETA'][1] : 0);
        $sheet->getStyle("A$row:B$row")->applyFromArray($borderStyle);
        $row++;

        // FALTANTE
        $faltanteBoleta = (isset($totalesTipoDoc['BOLETA'][2]) ? $totalesTipoDoc['BOLETA'][2] : 0) - (isset($totalesTipoDoc['BOLETA'][1]) ? $totalesTipoDoc['BOLETA'][1] : 0);
        $sheet->setCellValue("A$row", "FALTANTE");
        $sheet->setCellValue("B$row", $faltanteBoleta);
        $sheet->getStyle("A$row:B$row")->applyFromArray($boldStyle);
        if ($faltanteBoleta != 0) {
            $sheet->getStyle("A$row:B$row")->applyFromArray($redStyle);
        }
        $row += 2;

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

                $totalSire += $dif['total_sire'];
                $totalNubox += $dif['total_nubox'];
                $totalDif += $dif['diferencia'];
                $row++;
            }
            // Fila de totales
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

        // --- EXPORTAR ---
        $fechaActual = date('Y-m-d');
        $nombreMes = $mesSeleccionado ?: 'sin_mes';
        $nombreArchivo = "Reporte de Cuadres - {$nombreMes} - {$fechaActual}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
