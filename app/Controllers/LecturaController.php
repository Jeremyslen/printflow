<?php
require_once __DIR__ . '/../Models/Lectura.php';
require_once __DIR__ . '/../Models/Printer.php';
require_once __DIR__ . '/../Models/Umbral.php';
require_once __DIR__ . '/../Models/Mantenimiento.php';

class LecturaController {
    public function index() {
        $lecturaModel     = new Lectura();
        $printerModel     = new Printer();
        $umbralModel      = new Umbral();
        $mantenimientoModel = new Mantenimiento();

        // 1) Leer filtros y paginación
        $impresoraId = $_GET['impresora_id'] ?? null;
        $fecha       = $_GET['fecha']       ?? null;
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $perPage     = 10;
        $offset      = ($page - 1) * $perPage;

        // 2) Obtener total y páginas
        $totalRecords = $lecturaModel->contarConFiltros($impresoraId, $fecha);
        $totalPages   = (int)ceil($totalRecords / $perPage);

        // 3) Obtener lecturas paginadas
        $lecturas = $lecturaModel->obtenerConFiltros(
            $impresoraId,
            $fecha,
            $perPage,
            $offset
        );

        // 4) Obtener impresoras para el dropdown
        $impresoras = $printerModel->all();

        // 5) Calcular “Próx. Mant.” por impresora
        $nextMant = [];
        foreach ($impresoras as $imp) {
            $lastCount = $mantenimientoModel->lastForPrinter($imp['id']);
            $umbral    = $umbralModel->getForPrinter($imp['id']);
            $nextMant[$imp['id']] = $lastCount + $umbral;
        }

        return [
            'view' => __DIR__ . '/../Views/lectura/index.php',
            'vars' => [
                'lecturas'     => $lecturas,
                'impresoras'   => $impresoras,
                'impresoraId'  => $impresoraId,
                'fecha'        => $fecha,
                'page'         => $page,
                'totalPages'   => $totalPages,
                'nextMant'     => $nextMant,
            ]
        ];
    }
}
