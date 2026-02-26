<!-- app/Views/repuesto/index.php -->
<div class="card">
  <div class="card-header"><h2>Repuestos y Facturas</h2></div>
  <div class="card-body">
    <a href="index.php?controller=repuesto&action=add"
       class="btn btn-primary" style="margin-bottom:1rem;">
      ➕ Nuevo Repuesto
    </a>

    <form method="GET" class="filter-form" style="margin-bottom:1rem;">
      <input type="hidden" name="controller" value="repuesto">
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
      <table class="table" style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th>Impresora</th><th>Fecha</th><th>Descripción</th>
            <th>Factura</th><th>Fotos</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($repuestos): foreach($repuestos as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['impresora'], ENT_QUOTES,'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['fecha'],     ENT_QUOTES,'UTF-8') ?></td>
            <td><?= nl2br(htmlspecialchars($r['descripcion'],ENT_QUOTES)) ?></td>
            <td>
              <?php if ($r['archivo_factura']): ?>
                <a href="uploads/repuestos/facturas/<?= htmlspecialchars($r['archivo_factura'],ENT_QUOTES) ?>"
                   target="_blank">Ver PDF</a>
              <?php endif; ?>
            </td>
            <td>
              <?php foreach($r['fotos'] as $f): ?>
                <a href="uploads/repuestos/fotos/<?= htmlspecialchars($f,ENT_QUOTES) ?>"
                   target="_blank">📷</a>
              <?php endforeach; ?>
            </td>
            <td>
              <a href="index.php?controller=repuesto&action=edit&id=<?= $r['id'] ?>">✏️</a>
              <a href="index.php?controller=repuesto&action=delete&id=<?= $r['id'] ?>"
                 onclick="return confirm('¿Eliminar este repuesto?')">🗑️</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6" style="text-align:center;">No hay repuestos.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
