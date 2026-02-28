<section class="card">
  <h2>Уведомления</h2>
  <form method="get" action="/">
    <input type="hidden" name="route" value="notifications">
    <div class="cols-2">
      <div>
        <label>Приоритет</label>
        <select name="severity">
          <option value="">Все</option>
          <option value="low" <?= ($filters['severity'] ?? '') === 'low' ? 'selected' : '' ?>>Низкий</option>
          <option value="medium" <?= ($filters['severity'] ?? '') === 'medium' ? 'selected' : '' ?>>Средний</option>
          <option value="high" <?= ($filters['severity'] ?? '') === 'high' ? 'selected' : '' ?>>Высокий</option>
        </select>
      </div>
      <div>
        <label>Статус</label>
        <select name="status">
          <option value="">Все</option>
          <option value="unread" <?= ($filters['status'] ?? '') === 'unread' ? 'selected' : '' ?>>Непрочитанные</option>
          <option value="read" <?= ($filters['status'] ?? '') === 'read' ? 'selected' : '' ?>>Прочитанные</option>
        </select>
      </div>
    </div>
    <div class="cols-2">
      <button type="submit" class="btn-secondary">Применить</button>
      <a class="btn btn-secondary" href="/?route=notifications">Сбросить</a>
    </div>
  </form>
  <br>
  <?php
    $severityLabel = [
      'low' => 'Низкий',
      'medium' => 'Средний',
      'high' => 'Высокий',
    ];
    $severityClass = [
      'low' => 'status-ok',
      'medium' => 'status-warn',
      'high' => 'status-danger',
    ];
  ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Дата</th><th>Заголовок</th><th>Сообщение</th><th>Приоритет</th><th>Статус</th></tr></thead>
      <tbody>
      <?php if (empty($notifications)): ?>
        <tr>
          <td colspan="5" class="muted">Уведомлений по выбранным фильтрам нет.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($notifications as $n): ?>
          <tr>
            <td><?= e($n['created_at']) ?></td>
            <td><?= e($n['title']) ?></td>
            <td><?= e($n['message']) ?></td>
            <td><span class="status <?= e($severityClass[$n['severity']] ?? 'status-warn') ?>"><?= e($severityLabel[$n['severity']] ?? $n['severity']) ?></span></td>
            <td>
              <?php if ((int) $n['is_read'] === 1): ?>
                <span class="status status-ok">Прочитано</span>
              <?php else: ?>
                <button data-notification-read data-id="<?= (int) $n['id'] ?>" data-csrf="<?= e($csrfToken) ?>" class="btn-secondary">Отметить как прочитанное</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ((int) $pages > 1): ?>
    <p>
      <?php for ($i = 1; $i <= (int) $pages; $i++): ?>
        <a class="btn btn-secondary" href="/?route=notifications&page=<?= $i ?>&severity=<?= urlencode((string) ($filters['severity'] ?? '')) ?>&status=<?= urlencode((string) ($filters['status'] ?? '')) ?>"><?= $i ?></a>
      <?php endfor; ?>
    </p>
  <?php endif; ?>
</section>
