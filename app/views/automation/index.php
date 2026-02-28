<div class="grid">
  <section class="card span-6">
    <h2>Новое расписание</h2>
    <form method="post" action="/?route=automation/add">
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
      <label>Название</label>
      <input type="text" name="name" required>
      <label>Тип сценария</label>
      <select name="schedule_type">
        <option value="watering">Полив по времени</option>
        <option value="lighting">Освещение по времени</option>
      </select>
      <label>Время запуска</label>
      <input type="time" name="execute_time" required>
      <label class="checkbox-line"><input type="checkbox" name="is_active" checked> Включить сразу</label>
      <p class="muted">Если снять галочку, расписание сохранится в статусе «Пауза».</p>
      <button type="submit">Сохранить расписание</button>
    </form>
  </section>
  <section class="card span-6">
    <h2>Список расписаний</h2>
    <?php if (empty($schedules)): ?>
      <p class="muted">Пока нет расписаний. Создайте первое слева.</p>
    <?php else: ?>
      <?php
        $typeLabels = [
          'watering' => 'Полив',
          'lighting' => 'Освещение',
        ];
      ?>
      <ul>
        <?php foreach ($schedules as $s): ?>
          <li>
            <strong><?= e($s['name']) ?></strong> — <?= e($typeLabels[$s['schedule_type']] ?? $s['schedule_type']) ?> в <?= e($s['execute_time']) ?>
            <span class="status <?= (int) $s['is_active'] === 1 ? 'status-ok' : 'status-warn' ?>"><?= (int) $s['is_active'] === 1 ? 'Активно' : 'Пауза' ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>
