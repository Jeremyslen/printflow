<?php
// app/Views/umbral/index.php

// Variables disponibles:
//   $impresoras : array of ['id','nombre','ip']
//   $umbrales   : array printer_id => contador
?>

<div class="card">
  <div class="card-header">
    <h2>Configuración de Umbrales de Mantenimiento</h2>
  </div>
  <div class="card-body">
    <form method="post" action="?controller=umbral&action=update">
      <table class="table">
        <thead>
          <tr>
            <th>Impresora</th>
            <th>Umbral (páginas)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($impresoras as $imp): ?>
            <tr>
              <td><?= htmlspecialchars($imp['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <input
                  type="number"
                  name="umbral[<?= $imp['id'] ?>]"
                  value="<?= $umbrales[$imp['id']] ?? 0 ?>"
                  min="0"
                  style="width: 100px;"
                >
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <button type="submit" class="btn btn-primary">Guardar Umbrales</button>
    </form>
  </div>
</div>
