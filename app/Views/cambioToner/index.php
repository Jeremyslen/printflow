<!-- app/Views/cambioToner/index.php -->
<div class="card">
  <div class="card-header">
    <h2>Gestión de Cambios de Tóner</h2>
    <a href="index.php?controller=tonerInventario&action=index" class="btn btn-secondary">
      Ver Inventario
    </a>
  </div>

  <div class="card-body">
    
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">Cambio de tóner registrado correctamente. Stock actualizado.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
          if ($_GET['error'] == 'sin_stock') echo 'No hay stock disponible del tóner seleccionado.';
          elseif ($_GET['error'] == 'invalid_data') echo 'Datos inválidos.';
          else echo 'Error al registrar el cambio.';
        ?>
      </div>
    <?php endif; ?>


    <!-- Filtro por impresora -->
    <form method="GET" class="filter-form" style="margin-bottom: 1rem;">
      <input type="hidden" name="controller" value="cambioToner">
      <input type="hidden" name="action"     value="index">
      <div class="form-group">
        <label for="impresora_id_filter">Filtrar por Impresora:</label>
        <select name="impresora_id" id="impresora_id_filter">
          <option value="">Todas</option>
          <?php foreach($impresoras as $imp): ?>
            <option value="<?= $imp['id'] ?>"
              <?= ($impresoraId == $imp['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($imp['nombre'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-secondary">Filtrar</button>
    </form>

    <!-- Historial -->
    <h3>Historial de Cambios</h3>
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Impresora</th>
            <th>Modelo Tóner</th>
            <th>Color</th>
            <th>Fecha</th>
            <th>Contador</th>
            <th>Stock Actual (%)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($cambios)): foreach($cambios as $c): ?>
            <?php
              $pixeles = (int)$c['stock_actual'];
              $porcentaje = round(min($pixeles, 156) / 156 * 100);
            ?>
            <tr>
              <td><?= htmlspecialchars($c['impresora'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['toner_modelo'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['color'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= number_format($c['contador']) ?></td>
              <td><?= $porcentaje ?>%</td>
            </tr>
          <?php endforeach; else: ?>
            <tr>
              <td colspan="6" class="empty-state" style="text-align:center;">
                No hay cambios de tóner registrados.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <a href="index.php?controller=lectura&action=index"
       class="lectura-back-link"
       style="display:inline-block; margin-top:15px;">
      ← Volver al Historial de Lecturas
    </a>
  </div>
</div>

<style>
.registro-cambio {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}
.form-inline {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: flex-end;
}
.form-group {
  display: flex;
  flex-direction: column;
}
.alert {
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}
.alert-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}
.alert-error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}
</style>