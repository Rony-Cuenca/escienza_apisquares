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

            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoComprobante($mesSeleccionado);
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

        // Datos de sucursal
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
            // Obtén todos los comprobantes del mes
            $cuadres = Cuadre::obtenerCuadresPorMes($mesSeleccionado);
            foreach ($cuadres as $cuadre) {
                // Agrupa por sistema
                if ($cuadre['id_reporte'] == 2) {
                    $cuadresSIRE[] = $cuadre;
                } elseif ($cuadre['id_reporte'] == 1) {
                    $cuadresNUBOX[] = $cuadre;
                } elseif ($cuadre['id_reporte'] == 3) {
                    $cuadresEDSUITE[] = $cuadre;
                }
            }

            // Totales por tipo_comprobante y sistema (clave: [tipo_comprobante][id_reporte])
            $totalesTipoDoc = Cuadre::obtenerTotalesPorTipoComprobante($mesSeleccionado);

            // Totales por serie y sistema (clave: [serie][id_reporte])
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

        // Nombre del mes para el título
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
}
