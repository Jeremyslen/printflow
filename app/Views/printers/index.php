<?php
// app/views/printers/index.php
?>

<div class="card">
  <div class="card-header">
    <h2 class="mb-0">Lista de Impresoras</h2>
  </div>
  <div class="card-body">
    <a href="index.php?controller=printer&action=add" class="btn btn-primary mb-3 mr-2">+ Nueva impresora</a>
    <a href="index.php?controller=umbral&action=index" class="btn btn-secondary mb-3">⚙️ Umbrales</a>


    <table class="table table-bordered table-striped">
      <thead class="thead-light">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>IP</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($printers)): ?>
          <?php foreach ($printers as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['id']) ?></td>
              <td><?= htmlspecialchars($p['nombre']) ?></td>
              <td><?= htmlspecialchars($p['ip']) ?></td>
              <td>
                <a href="index.php?controller=printer&action=edit&id=<?= $p['id'] ?>"
                   class="btn btn-sm btn-warning">Editar</a>
                <a href="index.php?controller=printer&action=delete&id=<?= $p['id'] ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('¿Eliminar impresora?')">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center">No hay impresoras registradas.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
