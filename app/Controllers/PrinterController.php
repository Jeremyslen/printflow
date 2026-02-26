<?php
// app/controllers/PrinterController.php

require_once __DIR__ . '/../models/Printer.php';

class PrinterController {
    private $model;

    public function __construct() {
        $this->model = new Printer();
    }

    public function index() {
        $printers = $this->model->all();
        return [
            'view' => __DIR__ . '/../views/printers/index.php',
            'vars' => ['printers' => $printers]
        ];
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);
            $ip     = trim($_POST['ip']);

            // Validación servidor de IP
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $error_message = 'La dirección IP no es válida.';
                return [
                    'view' => __DIR__ . '/../views/printers/add.php',
                    'vars' => ['error_message' => $error_message]
                ];
            }

            $this->model->create($nombre, $ip);
            header('Location: index.php?controller=printer&action=index');
            exit;
        }

        return [
            'view' => __DIR__ . '/../views/printers/add.php',
            'vars' => []
        ];
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?controller=printer&action=index');
            exit;
        }

        $printer = $this->model->find($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);
            $ip     = trim($_POST['ip']);

            // Validación servidor de IP
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $error_message = 'La dirección IP no es válida.';
                // reenviamos los valores ingresados para que el usuario corrija
                $printer = ['id'=>$id, 'nombre'=>$nombre, 'ip'=>$ip];
                return [
                    'view' => __DIR__ . '/../views/printers/edit.php',
                    'vars' => ['printer' => $printer, 'error_message' => $error_message]
                ];
            }

            $this->model->update($id, $nombre, $ip);
            header('Location: index.php?controller=printer&action=index');
            exit;
        }

        return [
            'view' => __DIR__ . '/../views/printers/edit.php',
            'vars' => ['printer' => $printer]
        ];
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->delete($id);
        }
        header('Location: index.php?controller=printer&action=index');
        exit;
    }
}
