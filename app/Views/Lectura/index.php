<?php /** @var array $lecturas, $impresoras, $nextMant; int $impresoraId, $page, $totalPages; string $fecha */ ?>
<div class="card">
  <div class="card-header">
    <h2>Historial de Lecturas de Impresoras</h2>
  </div>
  <div class="card-body">

    <?php if (isset($_GET['actualizado'])): ?>
      <div class="alert success" style="background-color:#d4edda;padding:10px;border-radius:4px;color:#155724;margin-bottom:15px;">
        ✔ Lecturas actualizadas desde impresoras.
      </div>
    <?php endif; ?>

    <div style="margin-bottom:15px;">
      <a href="index.php?controller=cambioToner&action=index"
         class="btn btn-secondary"
         style="background:rgb(135,84,182);color:white;padding:8px 16px;border:none;border-radius:4px;text-decoration:none;cursor:pointer;">
        📄 Ver Cambios de Tóner
      </a>
    </div>

    <form action="?controller=scraper&action=ejecutar" method="post" style="margin-bottom:15px;">
      <button type="submit" style="background-color:#007bff;color:white;padding:8px 16px;border:none;border-radius:4px;cursor:pointer;">
        🔄 Actualizar ahora
      </button>
    </form>

    <!-- Filtros -->
    <form method="GET" class="filter-form" style="margin-bottom:15px;">
      <input type="hidden" name="controller" value="lectura">
      <input type="hidden" name="action"     value="index">
      <label>Impresora:</label>
      <select name="impresora_id">
        <option value="">Todas</option>
        <?php foreach ($impresoras as $imp): ?>
          <option value="<?= $imp['id'] ?>" <?= ($impresoraId == $imp['id'])?'selected':''?>>
            <?= htmlspecialchars($imp['nombre'], ENT_QUOTES,'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
      <label>Fecha:</label>
      <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
      <button type="submit">Filtrar</button>
    </form>

    <table class="table" style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th>Impresora</th>
          <th>Fecha / Hora</th>
          <th>Contador Total</th>
          <th>Próx. Mant.</th>
          <th>Tóner</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($lecturas)): ?>
        <?php foreach ($lecturas as $l): ?>
          <?php $id = $l['impresora_id']; ?>
          <tr>
            <td><?= htmlspecialchars($l['nombre_impresora'],ENT_QUOTES,'UTF-8')?></td>
            <td><?= htmlspecialchars($l['fecha_hora'],ENT_QUOTES,'UTF-8')?></td>
            <td><?= number_format($l['contador_total']) ?></td>
            <td><?= isset($nextMant[$id]) ? number_format($nextMant[$id]) : '-' ?></td>
            <td>
              <?php
                // Mapas para las barras
                $fieldMap = [
                  'Negro'    => 'toner_black',
                  'Cian'     => 'toner_cyan',
                  'Magenta'  => 'toner_magenta',
                  'Amarillo' => 'toner_yellow',
                ];
                $gifMap = [
                  'Negro'    => 'deviceStTnBarK.gif',
                  'Cian'     => 'deviceStTnBarC.gif',
                  'Magenta'  => 'deviceStTnBarM.gif',
                  'Amarillo' => 'deviceStTnBarY.gif',
                ];
                foreach ($gifMap as $name => $file):
                  // ancho en px limitado a 156
                  $px = min((int)($l[$fieldMap[$name]] ?? 0), 156);
              ?>
                <div style="display:flex;align-items:center;margin-bottom:6px;">
                  <div style="width:70px;flex-shrink:0;font-weight:bold;"><?= $name ?></div>
                  <?php if ($name==='Negro'): ?>
                    <div style="position:relative;width:156px;height:18px;background:#f9f9f9;border:1px solid #ccc;">
                      <!-- marcas y línea al 50% -->
                      <div style="position:absolute;left:0;top:-18px;font-size:10px;">0%</div>
                      <div style="position:absolute;left:78px;top:-18px;font-size:10px;">50%</div>
                      <div style="position:absolute;left:156px;top:-18px;font-size:10px;">100%</div>
                      <div style="position:absolute;left:78px;top:0;bottom:0;border-left:1px dashed #000;"></div>
                      <img src="http://<?= htmlspecialchars($l['ip'],ENT_QUOTES,'UTF-8') ?>/images/<?= $file ?>"
                           style="position:absolute;left:0;top:0;"
                           width="<?= $px ?>" height="18"
                           title="<?= round($px/1.56) ?>%">
                    </div>
                  <?php else: ?>
                    <div style="width:156px;height:18px;border:1px solid #ccc;background:#f9f9f9;">
                      <img src="http://<?= htmlspecialchars($l['ip'],ENT_QUOTES,'UTF-8') ?>/images/<?= $file ?>"
                           style="display:block;"
                           width="<?= $px ?>" height="18"
                           title="<?= round($px/1.56) ?>%">
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" style="text-align:center;">No hay registros de lecturas.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <!-- Paginación -->
    <?php if ($totalPages>1): ?>
      <div class="pagination" style="margin-top:15px;">
        <?php for ($p=1;$p<=$totalPages;$p++):
          $q = http_build_query([
            'controller'=>'lectura',
            'action'=>'index',
            'impresora_id'=>$impresoraId,
            'fecha'=>$fecha,
            'page'=>$p
          ]);
        ?>
          <a href="?<?= $q ?>" style="
             padding:5px 10px;margin-right:5px;
             text-decoration:none;border:1px solid #ccc;
             border-radius:3px;<?= $p===$page?'background:#007bff;color:white;':''?>">
            <?= $p ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

    <a href="index.php" style="display:inline-block;margin-top:15px;">← Volver al Dashboard</a>
  </div>
</div>
