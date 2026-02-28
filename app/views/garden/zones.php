<div class="grid">
  <section class="card span-6">
    <h2>Создать зону</h2>
    <form method="post" action="/?route=zones/add">
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
      <label>Название</label>
      <input type="text" name="name" required>
      <label>Тип</label>
      <select name="zone_type">
        <option value="комната">Комната</option>
        <option value="балкон">Балкон</option>
        <option value="теплица">Теплица</option>
        <option value="грядка">Грядка</option>
      </select>
      <label>Описание</label>
      <textarea name="description"></textarea>
      <button type="submit">Добавить зону</button>
    </form>
  </section>
  <section class="card span-6">
    <h2>Существующие зоны</h2>
    <?php if (empty($zones)): ?>
      <p class="muted">Пока нет зон. Создайте первую слева.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($zones as $z): ?>
          <li><strong><?= e($z['name']) ?></strong> (<?= e($z['zone_type']) ?>) <span class="muted"><?= e((string) $z['description']) ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>
