<div class="card">
  <div class="card-header">
    <h2><?= $repuesto ? 'Editar' : 'Nuevo' ?> Repuesto</h2>
  </div>
  <div class="card-body">
    <form method="post" enctype="multipart/form-data" class="form-grid">
      <!-- Impresora -->
      <div class="form-group">
        <label>Impresora</label>
        <select name="impresora_id" required>
          <option value="">-- selecciona --</option>
          <?php foreach($impresoras as $imp): ?>
            <option value="<?= $imp['id'] ?>"
              <?= ($repuesto && $repuesto['impresora_id']==$imp['id'])?'selected':''?>>
              <?= htmlspecialchars($imp['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Fecha / Hora -->
      <div class="form-group">
        <label>Fecha / Hora</label>
        <input type="datetime-local" name="fecha" required
          value="<?= $repuesto ? str_replace(' ','T',$repuesto['fecha']) : '' ?>">
      </div>

      <!-- Descripción (ocupa toda la fila) -->
      <div class="form-group full-width">
        <label>Descripción</label>
        <textarea name="descripcion" rows="4" required><?= htmlspecialchars($repuesto['descripcion'] ?? '') ?></textarea>
      </div>

      <!-- Factura PDF -->
      <div class="form-group">
        <label>Factura (PDF)</label>
        <input type="file" name="archivo_factura" accept=".pdf">
        <?php if (!empty($repuesto['archivo_factura'])): ?>
          <p>Actual: <a href="uploads/<?= htmlspecialchars($repuesto['archivo_factura']) ?>" target="_blank">Ver</a></p>
          <input type="hidden" name="current_factura" value="<?= htmlspecialchars($repuesto['archivo_factura']) ?>">
        <?php endif; ?>
      </div>

      <!-- Fotos (varias) -->
      <div class="form-group full-width">
        <label>Fotos (puede subir varias)</label>
        <input type="file" name="fotos[]" accept="image/*" multiple>
        <?php if (!empty($repuesto['fotos'])): ?>
          <p>Actuales:
            <?php foreach($repuesto['fotos'] as $f): ?>
              <a href="uploads/<?= htmlspecialchars($f) ?>" target="_blank">📷</a>
            <?php endforeach; ?>
          </p>
          <input type="hidden" name="current_fotos" value='<?= json_encode($repuesto['fotos']) ?>'>
        <?php endif; ?>
      </div>

      <!-- Botones (ocupan toda la fila) -->
      <div class="form-actions full-width">
        <button type="submit" class="btn btn-primary">
          <?= $repuesto ? 'Actualizar' : 'Guardar' ?>
        </button>
        <a href="index.php?controller=repuesto&action=index" class="btn btn-secondary">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>
