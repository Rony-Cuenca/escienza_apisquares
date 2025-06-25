<?php
require_once 'model/Cuadre.php';

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
}
