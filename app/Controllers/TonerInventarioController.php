<?php
// app/Controllers/TonerInventarioController.php
require_once __DIR__ . '/../Models/TonerInventario.php';

class TonerInventarioController {
    
    /**
     * Listar inventario de tóners
     */
    public function index() {
        $model = new TonerInventario();
        $toners = $model->all();

        return [
            'view' => __DIR__ . '/../Views/tonerInventario/index.php',
            'vars' => compact('toners')
        ];
    }

    /**
     * Formulario para agregar nuevo tóner
     */
    public function create() {
        return [
            'view' => __DIR__ . '/../Views/tonerInventario/form.php',
            'vars' => ['toner' => null, 'action' => 'create']
        ];
    }

    /**
     * Guardar nuevo tóner
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=tonerInventario&action=index');
            exit;
        }

        $model = new TonerInventario();
        $data = [
            'modelo' => trim($_POST['modelo'] ?? ''),
            'color' => trim($_POST['color'] ?? ''),
            'cantidad_disponible' => (int)($_POST['cantidad_disponible'] ?? 0),
            'compatible_impresoras' => trim($_POST['compatible_impresoras'] ?? '')
        ];

        if ($model->create($data)) {
            header('Location: index.php?controller=tonerInventario&action=index&success=1');
        } else {
            header('Location: index.php?controller=tonerInventario&action=index&error=1');
        }
        exit;
    }

    /**
     * Formulario para editar tóner existente
     */
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $model = new TonerInventario();
        $toner = $model->find($id);

        if (!$toner) {
            header('Location: index.php?controller=tonerInventario&action=index&error=not_found');
            exit;
        }

        return [
            'view' => __DIR__ . '/../Views/tonerInventario/form.php',
            'vars' => compact('toner') + ['action' => 'edit']
        ];
    }

    /**
     * Actualizar tóner existente
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=tonerInventario&action=index');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $model = new TonerInventario();
        
        $data = [
            'modelo' => trim($_POST['modelo'] ?? ''),
            'color' => trim($_POST['color'] ?? ''),
            'cantidad_disponible' => (int)($_POST['cantidad_disponible'] ?? 0),
            'compatible_impresoras' => trim($_POST['compatible_impresoras'] ?? '')
        ];

        if ($model->update($id, $data)) {
            header('Location: index.php?controller=tonerInventario&action=index&success=updated');
        } else {
            header('Location: index.php?controller=tonerInventario&action=index&error=update_failed');
        }
        exit;
    }

    /**
     * Eliminar tóner
     */
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $model = new TonerInventario();

        if ($model->delete($id)) {
            header('Location: index.php?controller=tonerInventario&action=index&success=deleted');
        } else {
            header('Location: index.php?controller=tonerInventario&action=index&error=delete_failed');
        }
        exit;
    }
}