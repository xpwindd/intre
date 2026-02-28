<div class="grid">
  <section class="card span-6">
    <h2>Добавить растение</h2>
    <form method="post" action="/?route=plants/add" id="plant-form">
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">

      <label>Название</label>
      <input type="text" name="name" required>

      <label>Культура</label>
      <select name="catalog_id" required>
        <?php foreach ($catalog as $c): ?>
          <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['category']) ?>)</option>
        <?php endforeach; ?>
      </select>

      <label>Зона</label>
      <div class="cols-2">
        <label>
          <input
            type="radio"
            name="zone_mode"
            value="existing"
            <?= !empty($zones) ? 'checked' : '' ?>
            <?= empty($zones) ? 'disabled' : '' ?>
          >
          Выбрать существующую
        </label>
        <label>
          <input
            type="radio"
            name="zone_mode"
            value="new"
            <?= empty($zones) ? 'checked' : '' ?>
          >
          Создать новую
        </label>
      </div>

      <div id="existing-zone-block" style="<?= empty($zones) ? 'display:none;' : '' ?>">
        <label>Существующая зона</label>
        <select name="zone_id" <?= empty($zones) ? 'disabled' : '' ?>>
          <?php foreach ($zones as $z): ?>
            <option value="<?= (int) $z['id'] ?>"><?= e($z['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="new-zone-block" style="<?= empty($zones) ? '' : 'display:none;' ?>">
        <label>Название новой зоны</label>
        <input type="text" name="new_zone_name" placeholder="Например: Подоконник кухня">

        <label>Тип зоны</label>
        <select name="new_zone_type">
          <option value="комната">Комната</option>
          <option value="балкон">Балкон</option>
          <option value="теплица">Теплица</option>
          <option value="грядка">Грядка</option>
        </select>

        <label>Описание зоны</label>
        <textarea name="new_zone_description" placeholder="Короткое описание условий"></textarea>
      </div>

      <div class="cols-3">
        <div><label>Влажность почвы %</label><input type="number" step="0.1" name="target_soil_humidity" value="60"></div>
        <div><label>Температура °C</label><input type="number" step="0.1" name="target_temperature" value="22"></div>
        <div><label>Свет (ч/сут)</label><input type="number" name="target_light_hours" value="12"></div>
      </div>

      <label>Дата посадки</label>
      <input type="date" name="planted_at" value="<?= e(date('Y-m-d')) ?>">

      <label>Стадия роста</label>
      <input type="text" name="stage" value="рост">

      <button type="submit">Сохранить</button>
    </form>
  </section>

  <section class="card span-6">
    <h2>Список растений</h2>
    <form method="get" action="/">
      <input type="hidden" name="route" value="plants">
      <div class="cols-2">
        <div>
          <label>Поиск</label>
          <input type="text" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Название растения">
        </div>
        <div>
          <label>Зона</label>
          <select name="zone_id">
            <option value="0">Все зоны</option>
            <?php foreach ($zones as $z): ?>
              <option value="<?= (int) $z['id'] ?>" <?= (int) ($filters['zone_id'] ?? 0) === (int) $z['id'] ? 'selected' : '' ?>><?= e($z['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="cols-2">
        <button type="submit" class="btn-secondary">Применить фильтр</button>
        <a class="btn btn-secondary" href="/?route=plants">Сбросить</a>
      </div>
    </form>
    <br>

    <div class="table-wrap">
      <table>
        <thead><tr><th>Название</th><th>Культура</th><th>Зона</th><th>Цели</th></tr></thead>
        <tbody>
        <?php if (empty($plants)): ?>
          <tr>
            <td colspan="4" class="muted">По выбранным фильтрам ничего не найдено.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($plants as $p): ?>
            <tr>
              <td><?= e($p['name']) ?><br><span class="muted"><?= e((string) $p['stage']) ?></span></td>
              <td><?= e((string) $p['catalog_name']) ?></td>
              <td><?= e((string) $p['zone_name']) ?></td>
              <td><?= e((string) $p['target_soil_humidity']) ?>% / <?= e((string) $p['target_temperature']) ?>°C / <?= e((string) $p['target_light_hours']) ?>ч</td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ((int) $pages > 1): ?>
      <p>
        <?php for ($i = 1; $i <= (int) $pages; $i++): ?>
          <a class="btn btn-secondary" href="/?route=plants&page=<?= $i ?>&q=<?= urlencode((string) ($filters['q'] ?? '')) ?>&zone_id=<?= (int) ($filters['zone_id'] ?? 0) ?>"><?= $i ?></a>
        <?php endfor; ?>
      </p>
    <?php endif; ?>
  </section>
</div>

<script>
  (function () {
    const form = document.getElementById('plant-form');
    if (!form) return;

    const existingRadio = form.querySelector('input[name="zone_mode"][value="existing"]');
    const newRadio = form.querySelector('input[name="zone_mode"][value="new"]');
    const existingBlock = document.getElementById('existing-zone-block');
    const newBlock = document.getElementById('new-zone-block');
    const zoneSelect = form.querySelector('select[name="zone_id"]');
    const zoneName = form.querySelector('input[name="new_zone_name"]');

    function toggleZoneMode() {
      const isNew = newRadio && newRadio.checked;
      if (existingBlock) existingBlock.style.display = isNew ? 'none' : '';
      if (newBlock) newBlock.style.display = isNew ? '' : 'none';
      if (zoneSelect) zoneSelect.disabled = isNew;
      if (zoneName) zoneName.required = isNew;
    }

    if (existingRadio) existingRadio.addEventListener('change', toggleZoneMode);
    if (newRadio) newRadio.addEventListener('change', toggleZoneMode);
    toggleZoneMode();
  })();
</script>
