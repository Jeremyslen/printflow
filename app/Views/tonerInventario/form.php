<!-- app/Views/tonerInventario/form.php -->
<div class="card">
  <div class="card-header">
    <h2><?= $toner ? 'Editar Tóner' : 'Agregar Nuevo Tóner' ?></h2>
  </div>

  <div class="card-body">
    <form method="POST" 
          action="index.php?controller=tonerInventario&action=<?= $toner ? 'update' : 'store' ?>">
      
      <?php if ($toner): ?>
        <input type="hidden" name="id" value="<?= $toner['id'] ?>">
      <?php endif; ?>

      <div class="form-group">
        <label for="modelo">Modelo del Tóner *</label>
        <input type="text" 
               id="modelo" 
               name="modelo" 
               class="form-control"
               value="<?= htmlspecialchars($toner['modelo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               required
               placeholder="Ej: Ricoh MP C2011">
      </div>

      <div class="form-group">
        <label for="color">Color *</label>
        <select id="color" name="color" class="form-control" required>
          <option value="">Seleccionar...</option>
          <option value="Negro" <?= ($toner['color'] ?? '') == 'Negro' ? 'selected' : '' ?>>Negro</option>
          <option value="Cyan" <?= ($toner['color'] ?? '') == 'Cyan' ? 'selected' : '' ?>>Cyan</option>
          <option value="Magenta" <?= ($toner['color'] ?? '') == 'Magenta' ? 'selected' : '' ?>>Magenta</option>
          <option value="Amarillo" <?= ($toner['color'] ?? '') == 'Amarillo' ? 'selected' : '' ?>>Amarillo</option>
        </select>
      </div>

      <div class="form-group">
        <label for="cantidad_disponible">Cantidad Disponible *</label>
        <input type="number" 
               id="cantidad_disponible" 
               name="cantidad_disponible" 
               class="form-control"
               value="<?= htmlspecialchars($toner['cantidad_disponible'] ?? '0', ENT_QUOTES, 'UTF-8') ?>"
               min="0"
               required>
      </div>

      <div class="form-group">
        <label for="compatible_impresoras">Compatible con (Impresoras)</label>
        <input type="text" 
               id="compatible_impresoras" 
               name="compatible_impresoras" 
               class="form-control"
               value="<?= htmlspecialchars($toner['compatible_impresoras'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Ej: Ricoh 1, Ricoh 2">
        <small>Opcional: Lista de impresoras compatibles</small>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">
          <?= $toner ? 'Actualizar' : 'Guardar' ?>
        </button>
        <a href="index.php?controller=tonerInventario&action=index" class="btn btn-secondary">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>

<style>
.form-group {
  margin-bottom: 1rem;
}
.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
}
.form-control {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}
.form-actions {
  margin-top: 1.5rem;
  display: flex;
  gap: 1rem;
}
small {
  color: #666;
  font-size: 0.85rem;
}
</style>