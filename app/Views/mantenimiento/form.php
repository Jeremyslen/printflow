<!-- app/views/mantenimiento/form.php -->
<div class="card">
  <div class="card-header">
    <h2><?= $mantenimiento ? 'Editar' : 'Nuevo' ?> Mantenimiento</h2>
  </div>
  <div class="card-body">
    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="form-grid" novalidate>
      <!-- Impresora -->
      <div class="form-group">
        <label>Impresora</label>
        <select name="impresora_id" required>
          <option value="">-- selecciona --</option>
          <?php foreach($impresoras as $imp): ?>
            <option value="<?= $imp['id'] ?>"
              <?= ($mantenimiento && $mantenimiento['impresora_id']==$imp['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($imp['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Tipo -->
      <div class="form-group">
        <label>Tipo</label>
        <select name="tipo" required>
          <option value="preventivo" <?= isset($mantenimiento['tipo']) && $mantenimiento['tipo']==='preventivo' ? 'selected' : '' ?>>Preventivo</option>
          <option value="correctivo" <?= isset($mantenimiento['tipo']) && $mantenimiento['tipo']==='correctivo' ? 'selected' : '' ?>>Correctivo</option>
        </select>
      </div>

      <!-- Empresa proveedora -->
      <div class="form-group">
        <label>Empresa proveedora</label>
        <input
          type="text"
          name="empresa"
          required
          value="<?= htmlspecialchars($mantenimiento['empresa'] ?? '') ?>">
      </div>

      <!-- RUC -->
      <div class="form-group">
        <label for="ruc">RUC</label>
        <input
          type="text"
          name="ruc"
          id="ruc"
          maxlength="13"
          required
          pattern="^[0-9]+$"
          inputmode="numeric"
          title="Sólo números"
          value="<?= htmlspecialchars($mantenimiento['ruc'] ?? '') ?>">
      </div>

      <!-- Fecha / Hora -->
      <div class="form-group">
        <label>Fecha / Hora</label>
        <input
          type="datetime-local"
          name="fecha"
          required
          value="<?= $mantenimiento ? str_replace(' ','T',$mantenimiento['fecha']) : '' ?>">
      </div>

      <!-- Contador -->
      <div class="form-group">
        <label>Contador en el momento</label>
        <input
          type="number"
          name="contador"
          required
          value="<?= htmlspecialchars($mantenimiento['contador'] ?? '') ?>">
      </div>

      <!-- Observaciones -->
      <div class="form-group full-width">
        <label>Observaciones</label>
        <textarea name="observaciones" rows="3"><?= htmlspecialchars($mantenimiento['observaciones'] ?? '') ?></textarea>
      </div>

      <!-- Informe adjunto -->
      <div class="form-group full-width">
        <label>Informe (PDF/imagen)</label>
        <input type="file" name="archivo_informe" accept=".pdf,image/*">
        <?php if (!empty($mantenimiento['archivo_informe'])): ?>
          <p>Actual:
            <a href="uploads/<?= htmlspecialchars($mantenimiento['archivo_informe']) ?>" target="_blank">Ver</a>
          </p>
          <input type="hidden" name="current_file" value="<?= htmlspecialchars($mantenimiento['archivo_informe']) ?>">
        <?php endif; ?>
      </div>

      <!-- Botones -->
      <div class="form-actions full-width">
        <button type="submit" class="btn btn-primary">
          <?= $mantenimiento ? 'Actualizar' : 'Guardar' ?>
        </button>
        <a href="index.php?controller=mantenimiento&action=index" class="btn btn-secondary">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>

<script>
// Bloquear todo excepto dígitos y teclas de edición en el campo RUC
document.getElementById('ruc').addEventListener('keydown', function(e) {
  const nav = ['Backspace','ArrowLeft','ArrowRight','Delete','Tab'];
  if (nav.includes(e.key) || /[0-9]/.test(e.key)) {
    // permitimos teclas de navegación y dígitos
    return;
  }
  // bloqueamos todo lo demás (letras, símbolos, etc.)
  e.preventDefault();
});
</script>
