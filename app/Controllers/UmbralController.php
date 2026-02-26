<?php
// app/Controllers/UmbralController.php

require_once __DIR__ . '/../Models/Printer.php';
require_once __DIR__ . '/../Models/Umbral.php';

class UmbralController {
    private $printerModel;
    private $umbralModel;

    public function __construct() {
        $this->printerModel = new Printer();
        $this->umbralModel  = new Umbral();
    }

    // GET /?controller=umbral&action=index
    public function index() {
        // Cargar todas las impresoras
        $impresoras = $this->printerModel->all();
        // Para cada impresora, obtener su umbral actual
        $umbrales = [];
        foreach ($impresoras as $imp) {
            $umbrales[$imp['id']] = $this->umbralModel->getForPrinter($imp['id']);
        }

        return [
            'view' => __DIR__ . '/../Views/umbral/index.php',
            'vars' => [
                'impresoras' => $impresoras,
                'umbrales'   => $umbrales,
            ]
        ];
    }

    // POST /?controller=umbral&action=update
    public function update() {
        // Recibimos array umbral[printer_id] => contador
        $data = $_POST['umbral'] ?? [];
        foreach ($data as $printerId => $valor) {
            $printerId = (int)$printerId;
            $valor     = max(0, (int)$valor);
            $this->umbralModel->upsert($printerId, $valor);
        }
        // Mensaje flash
        $_SESSION['success_message'] = 'Umbrales actualizados correctamente.';
        // Redirigir a lista
        header('Location: index.php?controller=umbral&action=index');
        exit;
    }
}
