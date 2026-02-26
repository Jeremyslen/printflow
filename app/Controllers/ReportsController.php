<?php
// app/Controllers/ReportsController.php

require_once __DIR__ . '/../Models/Lectura.php';
require_once __DIR__ . '/../Models/Printer.php';

class ReportsController {
    public function charts() {
        $lecturaModel = new Lectura();
        $printerModel = new Printer();

        // Leer filtro: 'current' o 'weekly'
        $view = isset($_GET['view']) && $_GET['view'] === 'weekly' ? 'weekly' : 'current';

        $impresoras  = $printerModel->all();
        $chartsData  = [];
        $maxPixels   = 156;  // 156px equivale a 100%

        foreach ($impresoras as $imp) {
            if ($view === 'weekly') {
                $rows = $lecturaModel->getWeeklyClosuresData($imp['id']);
            } else {
                $rows = $lecturaModel->getCurrentWeekData($imp['id']);
            }

            $labels       = [];
            $counters     = [];
            $tonerBlack   = [];
            $tonerCyan    = [];
            $tonerMagenta = [];
            $tonerYellow  = [];

            foreach ($rows as $r) {
                $labels[]   = date('Y-m-d H:i', strtotime($r['fecha_hora']));
                $counters[] = (int)$r['contador_total'];

                // Convertir píxeles a porcentaje (máx 156px → 100%)
                $pctBlack   = round(min($r['toner_black'], $maxPixels) / $maxPixels * 100);
                $pctCyan    = round(min($r['toner_cyan'],  $maxPixels) / $maxPixels * 100);
                $pctMagenta = round(min($r['toner_magenta'], $maxPixels) / $maxPixels * 100);
                $pctYellow  = round(min($r['toner_yellow'],  $maxPixels) / $maxPixels * 100);

                $tonerBlack[]   = $pctBlack;
                $tonerCyan[]    = $pctCyan;
                $tonerMagenta[] = $pctMagenta;
                $tonerYellow[]  = $pctYellow;
            }

            $chartsData[] = [
                'impresora'    => $imp['nombre'],
                'labels'       => $labels,
                'counters'     => $counters,
                'tonerBlack'   => $tonerBlack,
                'tonerCyan'    => $tonerCyan,
                'tonerMagenta' => $tonerMagenta,
                'tonerYellow'  => $tonerYellow,
            ];
        }

        return [
            'view' => __DIR__ . '/../Views/reports/charts.php',
            'vars' => [
                'chartsData' => $chartsData,
                'viewMode'   => $view
            ]
        ];
    }
}
