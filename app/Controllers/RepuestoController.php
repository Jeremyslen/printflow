<?php
// app/Controllers/RepuestoController.php

require_once __DIR__ . '/../Models/Repuesto.php';
require_once __DIR__ . '/../Models/Printer.php';

class RepuestoController {
    public function index() {
        $printerModel  = new Printer();
        $repuestoModel = new Repuesto();

        // transformar '' en null para que all(?int $impresoraId) lo acepte
        $raw = $_GET['impresora_id'] ?? '';
        $impresoraId = ($raw === '' ? null : (int)$raw);

        $impresoras  = $printerModel->all();
        $repuestos   = $repuestoModel->all($impresoraId);

        return [
            'view' => __DIR__ . '/../Views/repuesto/index.php',
            'vars' => compact('impresoras','repuestos','impresoraId')
        ];
    }

    public function add() {
        $printerModel  = new Printer();
        $repuestoModel = new Repuesto();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // preparar directorios
            $baseDir = __DIR__ . '/../../public/uploads/';
            $factDir = $baseDir . 'repuestos/facturas/';
            $fotoDir = $baseDir . 'repuestos/fotos/';
            foreach ([$factDir,$fotoDir] as $d) if (!is_dir($d)) mkdir($d,0755,true);

            // subir factura
            $facturaName = null;
            if (!empty($_FILES['archivo_factura']['tmp_name'])) {
                $facturaName = uniqid().'_'.basename($_FILES['archivo_factura']['name']);
                move_uploaded_file($_FILES['archivo_factura']['tmp_name'], $factDir.$facturaName);
            }

            // subir fotos
            $fotoNames = [];
            if (!empty($_FILES['fotos']['tmp_name'][0])) {
                foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp) {
                    $name = uniqid().'_'.basename($_FILES['fotos']['name'][$i]);
                    move_uploaded_file($tmp, $fotoDir.$name);
                    $fotoNames[] = $name;
                }
            }

            $data = [
                'impresora_id'   => $_POST['impresora_id'],
                'fecha'          => $_POST['fecha'],
                'descripcion'    => trim($_POST['descripcion']),
                'archivo_factura'=> $facturaName,
                'fotos'          => $fotoNames,
            ];
            $repuestoModel->create($data);

            $_SESSION['success_message'] = 'Repuesto registrado.';
            header('Location: index.php?controller=repuesto&action=index');
            exit;
        }

        $impresoras = $printerModel->all();
        return [
            'view' => __DIR__ . '/../Views/repuesto/form.php',
            'vars' => ['impresoras'=>$impresoras,'repuesto'=>null]
        ];
    }

    public function edit() {
        $printerModel  = new Printer();
        $repuestoModel = new Repuesto();
        $id            = (int)($_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // preparar directorios
            $baseDir = __DIR__ . '/../../public/uploads/';
            $factDir = $baseDir . 'repuestos/facturas/';
            $fotoDir = $baseDir . 'repuestos/fotos/';
            foreach ([$factDir,$fotoDir] as $d) if (!is_dir($d)) mkdir($d,0755,true);

            // factura
            $facturaName = $_POST['current_factura'] ?? null;
            if (!empty($_FILES['archivo_factura']['tmp_name'])) {
                $facturaName = uniqid().'_'.basename($_FILES['archivo_factura']['name']);
                move_uploaded_file($_FILES['archivo_factura']['tmp_name'], $factDir.$facturaName);
            }

            // fotos existentes + nuevas
            $existing = !empty($_POST['current_fotos'])
                      ? json_decode($_POST['current_fotos'], true)
                      : [];
            $newFotos = [];
            if (!empty($_FILES['fotos']['tmp_name'][0])) {
                foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp) {
                    $name = uniqid().'_'.basename($_FILES['fotos']['name'][$i]);
                    move_uploaded_file($tmp, $fotoDir.$name);
                    $newFotos[] = $name;
                }
            }
            $allFotos = array_merge($existing, $newFotos);

            $data = [
                'impresora_id'   => $_POST['impresora_id'],
                'fecha'          => $_POST['fecha'],
                'descripcion'    => trim($_POST['descripcion']),
                'archivo_factura'=> $facturaName,
                'fotos'          => $allFotos,
            ];
            $repuestoModel->update($id, $data);

            $_SESSION['success_message'] = 'Repuesto actualizado.';
            header('Location: index.php?controller=repuesto&action=index');
            exit;
        }

        $impresoras = $printerModel->all();
        $repuesto   = $repuestoModel->find($id);
        return [
            'view' => __DIR__ . '/../Views/repuesto/form.php',
            'vars' => compact('impresoras','repuesto')
        ];
    }

    public function delete() {
        $repuestoModel = new Repuesto();
        $id            = (int)($_GET['id'] ?? 0);
        if ($id) {
            $repuestoModel->delete($id);
            $_SESSION['success_message'] = 'Repuesto eliminado.';
        }
        header('Location: index.php?controller=repuesto&action=index');
        exit;
    }
}
