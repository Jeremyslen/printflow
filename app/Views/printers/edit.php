<!-- app/views/printers/edit.php -->
<div class="card">
  <div class="card-header">
    <h2 class="mb-0">Editar Impresora</h2>
  </div>
  <div class="card-body form-card">
    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <form action="index.php?controller=printer&action=edit&id=<?= $printer['id'] ?>" method="post" novalidate>
      <div class="form-group">
        <label for="nombre">Nombre</label>
        <input
          type="text"
          name="nombre"
          id="nombre"
          class="form-control"
          required
          value="<?= htmlspecialchars($printer['nombre']) ?>">
      </div>
      <div class="form-group">
        <label for="ip">Dirección IP</label>
        <input
          type="text"
          name="ip"
          id="ip"
          class="form-control"
          required
          placeholder="Ejemplo: 192.168.100.12"
          pattern="^(\d{1,3}\.){3}\d{1,3}$"
          title="Debe ser una dirección IPv4 válida, por ejemplo 192.168.0.1"
          value="<?= htmlspecialchars($printer['ip']) ?>">
      </div>
      <button type="submit" class="btn btn-primary mt-2">Actualizar</button>
      <a href="index.php?controller=printer&action=index" class="btn btn-secondary mt-2">Cancelar</a>
    </form>
  </div>
</div>

<script>
// Bloquear todo excepto dígitos, punto y teclas de navegación en el campo IP
document.getElementById('ip').addEventListener('keydown', function(e) {
  const nav = ['Backspace','ArrowLeft','ArrowRight','Delete','Tab'];
  if (nav.includes(e.key) || e.key === '.' || /[0-9]/.test(e.key)) {
    return;
  }
  e.preventDefault();
});
</script>
