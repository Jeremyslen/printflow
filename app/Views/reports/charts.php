
<?php 
// app/Views/reports/charts.php
$pageTitle = 'Gráficos de Uso';
?>
<div class="card">
  <div class="card-header">
    <h2>Evolución de Contador y Consumo de Tóner</h2>
  </div>
  <div class="card-body">

    <!-- Selector de filtro mejorado -->
    <div class="chart-filter">
      <form method="get" class="filter-container">
        <input type="hidden" name="controller" value="reports">
        <input type="hidden" name="action" value="charts">
        
        <div class="filter-group">
          <label for="view" class="filter-label">Ver datos:</label>
          <select name="view" id="view" class="filter-select" onchange="this.form.submit()">
            <option value="current" <?= $viewMode === 'current' ? 'selected' : '' ?>>Semana actual</option>
            <option value="weekly" <?= $viewMode === 'weekly' ? 'selected' : '' ?>>Cierres semanales</option>
          </select>
        </div>
      </form>


    <?php foreach ($chartsData as $idx => $d): ?>
      <h3><?= htmlspecialchars($d['impresora'], ENT_QUOTES, 'UTF-8') ?></h3>
      <div class="table-container" style="margin-bottom:2rem;">
        <canvas id="counterChart<?= $idx ?>"></canvas>
      </div>
      <div class="table-container" style="margin-bottom:3rem;">
        <canvas id="tonerChart<?= $idx ?>"></canvas>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Cargar Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const chartsData = <?= json_encode($chartsData, JSON_HEX_TAG) ?>;

  chartsData.forEach(function(d, idx) {
    // ————————————— Gráfico de Contador Total —————————————
    new Chart(document.getElementById('counterChart'+idx), {
      type: 'line',
      data: {
        labels: d.labels,
        datasets: [{
          label: 'Contador Total',
          data: d.counters,
          fill: false,
          borderColor: 'blue'
        }]
      },
      options: {
        responsive: true,
        scales: {
          x: { display: true, title: { display: true, text: 'Fecha / Hora' } }
        }
      }
    });

    // Gráfico de Consumo de Tóner por Color con tonos más oscuros
    new Chart(document.getElementById('tonerChart'+idx), {
      type: 'bar',
      data: {
        labels: d.labels,
        datasets: [
          {
            label: 'Negro',
            data: d.tonerBlack,
            backgroundColor: '#000000'
          },
          {
            label: 'Cian',
            data: d.tonerCyan,
            backgroundColor: '#00CED1'
          },
          {
            label: 'Magenta',
            data: d.tonerMagenta,
            backgroundColor: '#FF1493'
          },
          {
            label: 'Amarillo',
            data: d.tonerYellow,
            backgroundColor: '#FFD700'
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          x: {
            stacked: false,
            title: { display: true, text: 'Fecha / Hora' }
          },
          y: {
            beginAtZero: true,
            max: 100,
            title: { display: true, text: '% Tóner' }
          }
        }
      }
    });
  });
});
</script>
