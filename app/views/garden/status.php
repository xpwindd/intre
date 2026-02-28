<div class="grid">
  <section class="card span-6">
    <h2>Текущий статус среды</h2>
    <?php if (!empty($sensor)): ?>
      <p>Время: <?= e($sensor['reading_time']) ?></p>
      <p>Влажность почвы: <strong><?= e((string) $sensor['soil_humidity']) ?>%</strong></p>
      <p>Температура: <strong><?= e((string) $sensor['temperature']) ?>°C</strong></p>
      <p>Влажность воздуха: <strong><?= e((string) $sensor['air_humidity']) ?>%</strong></p>
      <p>Освещенность: <strong><?= e((string) $sensor['light_level']) ?> lx</strong></p>
    <?php else: ?>
      <p class="muted">Пока нет данных датчиков.</p>
    <?php endif; ?>
  </section>
  <section class="card span-6">
    <h2>Состояние устройств</h2>
    <?php if (empty($devices)): ?>
      <p class="muted">Устройств пока нет.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($devices as $d): ?>
          <li>
            <?= e($d['name']) ?>:
            <span class="status <?= $d['status'] === 'on' ? 'status-ok' : 'status-warn' ?>"><?= $d['status'] === 'on' ? 'включено' : 'выключено' ?></span>,
            режим <?= (int) $d['is_auto'] === 1 ? 'авто' : 'ручной' ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>
