<div class="grid">
  <section class="card span-12">
    <h2>Личный кабинет</h2>
    <p class="muted">Быстрый доступ к растениям, зонам и состоянию системы.</p>
    <div data-sensors-live class="muted">Загрузка онлайн-показателей...</div>
  </section>

  <section class="card span-6">
    <h3>Последние растения</h3>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Название</th><th>Культура</th><th>Зона</th><th>Стадия</th></tr></thead>
        <tbody>
        <?php if (empty($plants)): ?>
          <tr>
            <td colspan="4" class="muted">Пока нет растений. Добавьте первое в разделе «Растения».</td>
          </tr>
        <?php else: ?>
          <?php foreach ($plants as $p): ?>
            <tr>
              <td><?= e($p['name']) ?></td>
              <td><?= e((string) $p['catalog_name']) ?></td>
              <td><?= e((string) $p['zone_name']) ?></td>
              <td><?= e((string) $p['stage']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="card span-6">
    <h3>Зоны выращивания</h3>
    <?php if (empty($zones)): ?>
      <p class="muted">Зон пока нет. Добавьте новую зону в разделе «Растения».</p>
    <?php else: ?>
      <ul>
        <?php foreach ($zones as $z): ?>
          <li><?= e($z['name']) ?> (<?= e($z['zone_type']) ?>)</li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>
