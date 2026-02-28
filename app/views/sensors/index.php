<div class="grid">
  <section class="card span-12">
    <h2>Раздел датчиков</h2>
    <p class="muted">Мониторинг среды, история показаний и ручное добавление данных. Онлайн-блок обновляется каждые 15 секунд.</p>
    <div data-sensors-live class="flash flash-success">Загрузка онлайн-данных...</div>
  </section>

  <section class="card span-6">
    <h3>Текущие показатели</h3>
    <?php if (!empty($latest)): ?>
      <div class="kpi">
        <div class="item">
          <div class="muted">Влажность почвы</div>
          <div class="val" data-live-soil><?= e((string) $latest['soil_humidity']) ?>%</div>
          <?php $soilStatus = $statuses['soil'] ?? 'ok'; ?>
          <span data-live-soil-status class="status <?= $soilStatus === 'ok' ? 'status-ok' : ($soilStatus === 'low' ? 'status-warn' : 'status-danger') ?>">
            <?= $soilStatus === 'ok' ? 'Норма' : ($soilStatus === 'low' ? 'Низкая' : 'Высокая') ?>
          </span>
        </div>
        <div class="item">
          <div class="muted">Температура</div>
          <div class="val" data-live-temp><?= e((string) $latest['temperature']) ?>°C</div>
          <?php $tempStatus = $statuses['temperature'] ?? 'ok'; ?>
          <span data-live-temp-status class="status <?= $tempStatus === 'ok' ? 'status-ok' : ($tempStatus === 'low' ? 'status-warn' : 'status-danger') ?>">
            <?= $tempStatus === 'ok' ? 'Норма' : ($tempStatus === 'low' ? 'Низкая' : 'Высокая') ?>
          </span>
        </div>
        <div class="item">
          <div class="muted">Освещенность</div>
          <div class="val" data-live-light><?= e((string) $latest['light_level']) ?> lx</div>
          <?php $lightStatus = $statuses['light'] ?? 'ok'; ?>
          <span data-live-light-status class="status <?= $lightStatus === 'ok' ? 'status-ok' : ($lightStatus === 'low' ? 'status-warn' : 'status-danger') ?>">
            <?= $lightStatus === 'ok' ? 'Норма' : ($lightStatus === 'low' ? 'Низкая' : 'Высокая') ?>
          </span>
        </div>
      </div>
      <p class="muted">Последняя запись: <?= e((string) $latest['reading_time']) ?> · зона: <?= e((string) ($latest['zone_name'] ?? '—')) ?></p>
    <?php else: ?>
      <p class="muted">Показаний пока нет. Добавьте первое значение.</p>
    <?php endif; ?>
  </section>

  <section class="card span-6">
    <h3>Добавить показания</h3>
    <?php if (empty($zones)): ?>
      <p class="muted">Сначала создайте зону, чтобы привязывать показания датчиков.</p>
      <p><a class="btn btn-secondary" href="/?route=plants">Перейти в раздел «Растения»</a></p>
    <?php else: ?>
      <form method="post" action="/?route=sensors/add">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <label>Зона</label>
        <select name="zone_id" required>
          <option value="">Выберите зону</option>
          <?php foreach ($zones as $z): ?>
            <option value="<?= (int) $z['id'] ?>"><?= e($z['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="cols-2">
          <div><label>Влажность почвы (%)</label><input type="number" name="soil_humidity" min="0" max="100" step="0.1" required></div>
          <div><label>Температура (°C)</label><input type="number" name="temperature" min="-20" max="70" step="0.1" required></div>
        </div>
        <div class="cols-2">
          <div><label>Влажность воздуха (%)</label><input type="number" name="air_humidity" min="0" max="100" step="0.1" required></div>
          <div><label>Освещенность (lx)</label><input type="number" name="light_level" min="0" max="200000" step="1" required></div>
        </div>
        <button type="submit">Сохранить показания</button>
      </form>
    <?php endif; ?>
  </section>

  <section class="card span-12">
    <h3>Фильтрация истории</h3>
    <form method="get" action="/">
      <input type="hidden" name="route" value="sensors">
      <div class="cols-3">
        <div>
          <label>Зона</label>
          <select name="zone_id">
            <option value="0">Все зоны</option>
            <?php foreach ($zones as $z): ?>
              <option value="<?= (int) $z['id'] ?>" <?= (int) ($filters['zone_id'] ?? 0) === (int) $z['id'] ? 'selected' : '' ?>><?= e($z['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div><label>С даты</label><input type="date" name="date_from" value="<?= e((string) ($filters['date_from'] ?? '')) ?>"></div>
        <div><label>По дату</label><input type="date" name="date_to" value="<?= e((string) ($filters['date_to'] ?? '')) ?>"></div>
      </div>
      <div class="cols-2">
        <button type="submit" class="btn-secondary">Применить фильтр</button>
        <a class="btn btn-secondary" href="/?route=sensors">Сбросить</a>
      </div>
    </form>
  </section>

  <section class="card span-12">
    <h3>Тренд последних 24 измерений (влажность почвы)</h3>
    <?php if (empty($trend)): ?>
      <p class="muted">Недостаточно данных для построения тренда.</p>
    <?php endif; ?>
    <canvas id="soilTrendChart" height="110" style="width:100%; display:block; background: rgba(10,27,25,0.5); border:1px solid var(--line); border-radius:12px;"></canvas>
    <script type="application/json" id="soilTrendData"><?= json_encode($trend, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
  </section>

  <section class="card span-12">
    <h3>История показаний</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Дата и время</th>
            <th>Зона</th>
            <th>Влажность почвы</th>
            <th>Температура</th>
            <th>Влажность воздуха</th>
            <th>Освещенность</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($readings)): ?>
            <tr>
              <td colspan="6" class="muted">История показаний по выбранным фильтрам пуста.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($readings as $r): ?>
              <tr>
                <td><?= e((string) $r['reading_time']) ?></td>
                <td><?= e((string) ($r['zone_name'] ?? '—')) ?></td>
                <td><?= e((string) $r['soil_humidity']) ?>%</td>
                <td><?= e((string) $r['temperature']) ?>°C</td>
                <td><?= e((string) $r['air_humidity']) ?>%</td>
                <td><?= e((string) $r['light_level']) ?> lx</td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ((int) $pages > 1): ?>
      <p>
        <?php for ($i = 1; $i <= (int) $pages; $i++): ?>
          <a class="btn btn-secondary" href="/?route=sensors&page=<?= $i ?>&zone_id=<?= (int) ($filters['zone_id'] ?? 0) ?>&date_from=<?= urlencode((string) ($filters['date_from'] ?? '')) ?>&date_to=<?= urlencode((string) ($filters['date_to'] ?? '')) ?>"><?= $i ?></a>
        <?php endfor; ?>
      </p>
    <?php endif; ?>
  </section>
</div>

<script>
  (function () {
    const dataNode = document.getElementById('soilTrendData');
    const canvas = document.getElementById('soilTrendChart');
    if (!dataNode || !canvas) return;

    const data = JSON.parse(dataNode.textContent || '[]');
    if (!Array.isArray(data) || data.length === 0) return;

    const ctx = canvas.getContext('2d');
    const width = canvas.width = canvas.clientWidth;
    const height = canvas.height = 220;
    const padding = 24;

    const values = data.map((x) => Number(x.soil_humidity || 0));
    const min = Math.min(...values, 0);
    const max = Math.max(...values, 100);
    const range = max - min || 1;

    ctx.clearRect(0, 0, width, height);
    ctx.strokeStyle = 'rgba(39,211,162,0.25)';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, height - padding);
    ctx.lineTo(width - padding, height - padding);
    ctx.stroke();

    ctx.strokeStyle = '#27d3a2';
    ctx.lineWidth = 2;
    ctx.beginPath();
    values.forEach((v, i) => {
      const x = padding + (i / Math.max(values.length - 1, 1)) * (width - padding * 2);
      const y = height - padding - ((v - min) / range) * (height - padding * 2);
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    });
    ctx.stroke();

    ctx.fillStyle = '#9ec0b8';
    ctx.font = '12px Segoe UI';
    ctx.fillText(`Мин: ${min.toFixed(1)}%`, padding, 14);
    ctx.fillText(`Макс: ${max.toFixed(1)}%`, width - 88, 14);
  })();
</script>
