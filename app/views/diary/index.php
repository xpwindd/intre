<div class="grid">
  <section class="card span-6">
    <h2>Новая запись дневника</h2>
    <?php if (empty($plants)): ?>
      <p class="muted">Чтобы вести дневник, сначала добавьте хотя бы одно растение.</p>
      <p><a class="btn btn-secondary" href="/?route=plants">Перейти в раздел «Растения»</a></p>
    <?php else: ?>
      <form method="post" action="/?route=diary/add" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <label>Растение</label>
        <select name="plant_id" required>
          <?php foreach ($plants as $p): ?>
            <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <label>Дата</label>
        <input type="date" name="entry_date" value="<?= e(date('Y-m-d')) ?>">
        <div class="cols-2">
          <div><label>Высота (см)</label><input type="number" name="height_cm" step="0.1"></div>
          <div><label>Состояние</label><input type="text" name="condition_text" placeholder="Хорошее"></div>
        </div>
        <label>Заметка</label>
        <textarea name="note" required></textarea>
        <div class="cols-2">
          <div><label>Событие ухода</label><input type="text" name="care_event_type" value="полив"></div>
          <div><label>Комментарий ухода</label><input type="text" name="care_note"></div>
        </div>
        <label>Фото (опционально)</label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
        <button type="submit">Сохранить запись</button>
      </form>
    <?php endif; ?>
  </section>
  <section class="card span-6">
    <h2>Таймлайн роста</h2>
    <form method="get" action="/">
      <input type="hidden" name="route" value="diary">
      <div class="cols-3">
        <div>
          <label>Растение</label>
          <select name="plant_id">
            <option value="0">Все</option>
            <?php foreach ($plants as $p): ?>
              <option value="<?= (int) $p['id'] ?>" <?= (int) ($filters['plant_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div><label>С даты</label><input type="date" name="date_from" value="<?= e((string) ($filters['date_from'] ?? '')) ?>"></div>
        <div><label>По дату</label><input type="date" name="date_to" value="<?= e((string) ($filters['date_to'] ?? '')) ?>"></div>
      </div>
      <div class="cols-2">
        <button type="submit" class="btn-secondary">Фильтровать</button>
        <a class="btn btn-secondary" href="/?route=diary">Сбросить</a>
      </div>
    </form>
    <br>
    <div class="timeline">
      <?php if (empty($entries)): ?>
        <p class="muted">Записей пока нет.</p>
      <?php else: ?>
        <?php foreach ($entries as $e): ?>
          <div class="entry">
            <strong><?= e($e['plant_name']) ?></strong> · <?= e($e['entry_date']) ?>
            <div class="muted">Высота: <?= e((string) $e['height_cm']) ?> см · Состояние: <?= e((string) $e['condition_text']) ?></div>
            <div><?= e((string) $e['note']) ?></div>
            <?php if (!empty($e['photo_path'])): ?>
              <div><a href="<?= e($e['photo_path']) ?>" target="_blank">Открыть фото</a></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>
