<?php
// app/Controllers/MantenimientoController.php

require_once __DIR__ . '/../Models/Mantenimiento.php';
require_once __DIR__ . '/../Models/Printer.php';
require_once __DIR__ . '/../../config/db.php';

class MantenimientoController {
    public function index() {
        $printerModel   = new Printer();
        $mModel         = new Mantenimiento();

        // si viene vacío => null, si no => entero
        $raw = $_GET['impresora_id'] ?? '';
        $impresoraId = ($raw === '' ? null : (int)$raw);

        $impresoras     = $printerModel->all();
        $mantenimientos = $mModel->all($impresoraId);

        return [
            'view' => __DIR__ . '/../Views/mantenimiento/index.php',
            'vars' => compact('impresoras','mantenimientos','impresoraId')
        ];
    }

    public function add() {
        $printerModel = new Printer();
        $mModel       = new Mantenimiento();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ruc = trim($_POST['ruc']);
            if (!preg_match('/^[0-9]+$/', $ruc)) {
                $error_message = 'El RUC sólo debe contener dígitos.';
                $impresoras = $printerModel->all();
                return [
                    'view' => __DIR__ . '/../Views/mantenimiento/form.php',
                    'vars' => [
                        'impresoras'    => $impresoras,
                        'mantenimiento' => $_POST,
                        'error_message'=> $error_message
                    ]
                ];
            }

            $uploadDir = __DIR__ . '/../../public/uploads/mantenimientos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

            $fileName = null;
            if (!empty($_FILES['archivo_informe']['tmp_name'])) {
                $fileName = uniqid().'_'.basename($_FILES['archivo_informe']['name']);
                move_uploaded_file($_FILES['archivo_informe']['tmp_name'], $uploadDir.$fileName);
            }

            $data = [
                'impresora_id'   => $_POST['impresora_id'],
                'tipo'           => $_POST['tipo'],
                'empresa'        => $_POST['empresa'],
                'ruc'            => $ruc,
                'fecha'          => $_POST['fecha'],
                'contador'       => $_POST['contador'],
                'observaciones'  => $_POST['observaciones'] ?? '',
                'archivo_informe'=> $fileName
            ];
            $mModel->create($data);

            // limpia alerta mantenimiento
            $pdo = Database::connect();
            $pdo->prepare("
                DELETE FROM alertas_toner
                 WHERE impresora_id = ?
                   AND color = 'mantenimiento'
                   AND tipo_alerta = 'bajo'
            ")->execute([ (int)$_POST['impresora_id'] ]);

            $_SESSION['success_message'] = 'Mantenimiento creado.';
            header('Location: index.php?controller=mantenimiento&action=index');
            exit;
        }

        $impresoras = $printerModel->all();
        return [
            'view' => __DIR__ . '/../Views/mantenimiento/form.php',
            'vars' => ['impresoras' => $impresoras, 'mantenimiento' => null]
        ];
    }

    public function edit() {
        $printerModel = new Printer();
        $mModel       = new Mantenimiento();
        $id           = (int)($_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ruc = trim($_POST['ruc']);
            if (!preg_match('/^[0-9]+$/', $ruc)) {
                $error_message = 'El RUC sólo debe contener dígitos.';
                $impresoras = $printerModel->all();
                $mantenimiento = array_merge($_POST, ['id'=>$id]);
                return [
                    'view' => __DIR__ . '/../Views/mantenimiento/form.php',
                    'vars' => [
                        'impresoras'    => $impresoras,
                        'mantenimiento' => $mantenimiento,
                        'error_message'=> $error_message
                    ]
                ];
            }

            $uploadDir = __DIR__ . '/../../public/uploads/mantenimientos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

            $fileName = $_POST['current_file'] ?? null;
            if (!empty($_FILES['archivo_informe']['tmp_name'])) {
                $fileName = uniqid().'_'.basename($_FILES['archivo_informe']['name']);
                move_uploaded_file($_FILES['archivo_informe']['tmp_name'], $uploadDir.$fileName);
            }

            $data = [
                'impresora_id'   => $_POST['impresora_id'],
                'tipo'           => $_POST['tipo'],
                'empresa'        => $_POST['empresa'],
                'ruc'            => $ruc,
                'fecha'          => $_POST['fecha'],
                'contador'       => $_POST['contador'],
                'observaciones'  => $_POST['observaciones'] ?? '',
                'archivo_informe'=> $fileName
            ];
            $mModel->update($id, $data);

            $pdo = Database::connect();
            $pdo->prepare("
                DELETE FROM alertas_toner
                 WHERE impresora_id = ?
                   AND color = 'mantenimiento'
                   AND tipo_alerta = 'bajo'
            ")->execute([ (int)$_POST['impresora_id'] ]);

            $_SESSION['success_message'] = 'Mantenimiento actualizado.';
            header('Location: index.php?controller=mantenimiento&action=index');
            exit;
        }

        $impresoras    = $printerModel->all();
        $mantenimiento = $mModel->find($id);
        return [
            'view' => __DIR__ . '/../Views/mantenimiento/form.php',
            'vars' => compact('impresoras','mantenimiento')
        ];
    }

    public function delete() {
        $mModel = new Mantenimiento();
        $id     = (int)($_GET['id'] ?? 0);
        if ($id) {
            $mModel->delete($id);
            $_SESSION['success_message'] = 'Mantenimiento eliminado.';
        }
        header('Location: index.php?controller=mantenimiento&action=index');
        exit;
    }
}
