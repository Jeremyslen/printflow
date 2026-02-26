<!-- app/Views/tonerInventario/index.php -->
<div class="card">
  <div class="card-header">
    <h2>Inventario de Tóners</h2>
    <a href="index.php?controller=tonerInventario&action=create" class="btn btn-primary">
      + Agregar Tóner
    </a>
  </div>

  <div class="card-body">
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">
        <?php
          if ($_GET['success'] == 'updated') echo 'Tóner actualizado correctamente';
          elseif ($_GET['success'] == 'deleted') echo 'Tóner eliminado correctamente';
          else echo 'Tóner agregado correctamente';
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        Hubo un error al procesar la solicitud.
      </div>
    <?php endif; ?>

    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Modelo</th>
            <th>Color</th>
            <th>Stock Disponible</th>
            <th>Compatible con</th>
            <th>Última Actualización</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($toners)): foreach($toners as $t): ?>
            <tr>
              <td><?= htmlspecialchars($t['modelo'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <span class="badge badge-<?= strtolower($t['color']) ?>">
                  <?= htmlspecialchars($t['color'], ENT_QUOTES, 'UTF-8') ?>
                </span>
              </td>
              <td>
                <strong style="<?= $t['cantidad_disponible'] == 0 ? 'color: red;' : '' ?>">
                  <?= $t['cantidad_disponible'] ?>
                </strong>
              </td>
              <td><?= htmlspecialchars($t['compatible_impresoras'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= date('d/m/Y H:i', strtotime($t['ultima_actualizacion'])) ?></td>
              <td>
                <a href="index.php?controller=tonerInventario&action=edit&id=<?= $t['id'] ?>" 
                   class="btn btn-sm btn-secondary">
                  Editar
                </a>
                <a href="index.php?controller=tonerInventario&action=delete&id=<?= $t['id'] ?>" 
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('¿Seguro que deseas eliminar este tóner?')">
                  Eliminar
                </a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr>
              <td colspan="6" class="empty-state" style="text-align:center;">
                No hay tóners registrados en el inventario.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}
.badge-negro, .badge-black { background: #000; color: #fff; }
.badge-cyan { background: #00bcd4; color: #fff; }
.badge-magenta { background: #e91e63; color: #fff; }
.badge-amarillo, .badge-yellow { background: #ffeb3b; color: #000; }
</style>