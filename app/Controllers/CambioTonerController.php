<?php
// app/Controllers/CambioTonerController.php
require_once __DIR__ . '/../Models/CambioToner.php';
require_once __DIR__ . '/../Models/Printer.php';
require_once __DIR__ . '/../Models/TonerInventario.php';

class CambioTonerController {
    
    public function index() {
        $model        = new CambioToner();
        $printerModel = new Printer();
        $tonerModel   = new TonerInventario();

        $impresoraId = isset($_GET['impresora_id'])
                     ? (int)$_GET['impresora_id']
                     : null;

        $cambios    = $model->all($impresoraId);
        $impresoras = $printerModel->all();
        $tonersDisponibles = $tonerModel->disponibles();

        return [
            'view' => __DIR__ . '/../Views/cambioToner/index.php',
            'vars' => compact('cambios', 'impresoras', 'impresoraId', 'tonersDisponibles')
        ];
    }

    /**
     * Registrar un nuevo cambio de tóner y descontar del inventario
     */
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=cambioToner&action=index');
            exit;
        }

        $tonerInventarioId = (int)($_POST['toner_inventario_id'] ?? 0);
        $impresoraId = (int)($_POST['impresora_id'] ?? 0);
        $contador = (int)($_POST['contador'] ?? 0);

        // Validar datos
        if (!$tonerInventarioId || !$impresoraId) {
            header('Location: index.php?controller=cambioToner&action=index&error=invalid_data');
            exit;
        }

        $tonerModel = new TonerInventario();
        $cambioModel = new CambioToner();

        // Obtener info del tóner
        $toner = $tonerModel->find($tonerInventarioId);
        if (!$toner || $toner['cantidad_disponible'] <= 0) {
            header('Location: index.php?controller=cambioToner&action=index&error=sin_stock');
            exit;
        }

        // Registrar el cambio
        if ($cambioModel->registrar($impresoraId, $toner['color'], $contador, $tonerInventarioId)) {
            // Descontar del inventario
            $tonerModel->descontar($tonerInventarioId);
            header('Location: index.php?controller=cambioToner&action=index&success=1');
        } else {
            header('Location: index.php?controller=cambioToner&action=index&error=1');
        }
        exit;
    }
}