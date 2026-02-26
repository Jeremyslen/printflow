<!-- app/Views/mantenimiento/index.php -->
<div class="card">
  <div class="card-header"><h2>Listado de Mantenimientos</h2></div>
  <div class="card-body">
    <a href="index.php?controller=mantenimiento&action=add" class="btn btn-primary" style="margin-bottom:1rem;">
      ➕ Nuevo
    </a>

    <form method="GET" class="filter-form" style="margin-bottom:1rem;">
      <input type="hidden" name="controller" value="mantenimiento">
      <input type="hidden" name="action"     value="index">
      <label for="impresora_id">Impresora:</label>
      <select name="impresora_id" id="impresora_id">
        <option value="">Todas</option>
        <?php foreach($impresoras as $imp): ?>
          <option value="<?= $imp['id'] ?>"
            <?= ($impresoraId === $imp['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($imp['nombre'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-secondary">Filtrar</button>
    </form>

    <div class="table-container">
      <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr>
            <th>Impresora</th><th>Tipo</th><th>Fecha</th>
            <th>Contador</th><th>Informe</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($mantenimientos): foreach($mantenimientos as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['impresora'], ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($m['tipo'],      ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($m['fecha'],     ENT_QUOTES,'UTF-8') ?></td>
            <td><?= number_format($m['contador']) ?></td>
            <td>
              <?php if ($m['archivo_informe']): ?>
                <a href="uploads/mantenimientos/<?= htmlspecialchars($m['archivo_informe'],ENT_QUOTES) ?>" target="_blank">Ver</a>
              <?php endif; ?>
            </td>
            <td>
              <a href="index.php?controller=mantenimiento&action=edit&id=<?= $m['id'] ?>">✏️</a>
              <a href="index.php?controller=mantenimiento&action=delete&id=<?= $m['id'] ?>"
                 onclick="return confirm('¿Eliminar mantenimiento?')">🗑️</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6" style="text-align:center;">No hay mantenimientos.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
