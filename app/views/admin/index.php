<div class="grid">
  <section class="card span-12">
    <h2>Админ-панель</h2>
    <p class="muted">Управление пользователями, устройствами, уведомлениями и аудитом действий системы.</p>
    <div class="kpi">
      <div class="item"><div class="muted">Пользователи</div><div class="val"><?= (int) $stats['users'] ?></div></div>
      <div class="item"><div class="muted">Растения</div><div class="val"><?= (int) $stats['plants'] ?></div></div>
      <div class="item"><div class="muted">Устройства</div><div class="val"><?= (int) $stats['devices'] ?></div></div>
    </div>
    <p><span class="status status-danger">Непрочитанные уведомления: <?= (int) $stats['unread_notifications'] ?></span></p>
  </section>

  <section class="card span-6">
    <h3>Пользователи и роли</h3>
    <form method="get" action="/">
      <input type="hidden" name="route" value="admin">
      <div class="cols-2">
        <div>
          <label>Поиск (имя/email)</label>
          <input type="text" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        </div>
        <div>
          <label>Роль</label>
          <select name="role">
            <option value="">Все</option>
            <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>admin</option>
            <option value="user" <?= ($filters['role'] ?? '') === 'user' ? 'selected' : '' ?>>user</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn-secondary">Фильтровать</button>
    </form>
    <br>

    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>Пользователь</th><th>Роль</th><th>Стат</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int) $u['id'] ?></td>
              <td>
                <strong><?= e($u['name']) ?></strong><br>
                <span class="muted"><?= e($u['email']) ?></span>
              </td>
              <td>
                <form method="post" action="/?route=admin/users/role">
                  <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                  <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                  <select name="role_id">
                    <?php foreach ($roles as $r): ?>
                      <option value="<?= (int) $r['id'] ?>" <?= (int) $u['role_id'] === (int) $r['id'] ? 'selected' : '' ?>>
                        <?= e($r['slug']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div style="margin-top:8px;">
                    <button type="submit" class="btn-secondary" style="width:auto;">Сменить</button>
                  </div>
                </form>
              </td>
              <td>
                <span class="muted">Растений: <?= (int) $u['plants_count'] ?></span><br>
                <span class="muted">Устройств: <?= (int) $u['devices_count'] ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="card span-6">
    <h3>Устройства (глобально)</h3>
    <form method="get" action="/">
      <input type="hidden" name="route" value="admin">
      <div class="cols-2">
        <div>
          <label>Статус устройства</label>
          <select name="device_status">
            <option value="">Все</option>
            <option value="on" <?= ($filters['device_status'] ?? '') === 'on' ? 'selected' : '' ?>>Включено</option>
            <option value="off" <?= ($filters['device_status'] ?? '') === 'off' ? 'selected' : '' ?>>Выключено</option>
          </select>
        </div>
        <div style="display:flex;align-items:flex-end;">
          <button type="submit" class="btn-secondary">Фильтровать</button>
        </div>
      </div>
    </form>
    <br>

    <div class="table-wrap">
      <table>
        <thead><tr><th>Устройство</th><th>Владелец</th><th>Статус</th><th>Упр.</th></tr></thead>
        <tbody>
          <?php foreach ($devices as $d): ?>
            <tr>
              <td>
                <strong><?= e($d['name']) ?></strong><br>
                <span class="muted"><?= e($d['device_type']) ?> · зона: <?= e((string) ($d['zone_name'] ?? '—')) ?></span>
              </td>
              <td><?= e($d['user_name']) ?><br><span class="muted"><?= e($d['user_email']) ?></span></td>
              <td>
                <span class="status <?= $d['status'] === 'on' ? 'status-ok' : 'status-warn' ?>">
                  <?= $d['status'] === 'on' ? 'Вкл' : 'Выкл' ?>
                </span>
              </td>
              <td>
                <form method="post" action="/?route=admin/devices/toggle">
                  <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                  <input type="hidden" name="device_id" value="<?= (int) $d['id'] ?>">
                  <button type="submit" class="btn-secondary" style="width:auto;">
                    <?= $d['status'] === 'on' ? 'Выключить' : 'Включить' ?>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="card span-6">
    <h3>Уведомления (модерация)</h3>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Дата</th><th>Пользователь</th><th>Сообщение</th><th>Статус</th></tr></thead>
        <tbody>
          <?php foreach ($notifications as $n): ?>
            <tr>
              <td><?= e((string) $n['created_at']) ?></td>
              <td><?= e((string) $n['user_email']) ?></td>
              <td>
                <strong><?= e((string) $n['title']) ?></strong><br>
                <span class="muted"><?= e((string) $n['message']) ?></span>
              </td>
              <td>
                <?php if ((int) $n['is_read'] === 1): ?>
                  <span class="status status-ok">Прочитано</span>
                <?php else: ?>
                  <form method="post" action="/?route=admin/notifications/read">
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="notification_id" value="<?= (int) $n['id'] ?>">
                    <button type="submit" class="btn-secondary" style="width:auto;">Отметить</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="card span-6">
    <h3>Журнал действий</h3>
    <form method="get" action="/">
      <input type="hidden" name="route" value="admin">
      <label>Поиск по действию/сообщению/email</label>
      <input type="text" name="log_q" value="<?= e((string) ($filters['log_q'] ?? '')) ?>">
      <button type="submit" class="btn-secondary">Найти</button>
    </form>
    <br>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Время</th><th>Пользователь</th><th>Действие</th><th>Сообщение</th></tr></thead>
        <tbody>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td><?= e((string) $l['created_at']) ?></td>
              <td><?= e((string) ($l['user_email'] ?? 'system')) ?></td>
              <td><?= e((string) $l['action']) ?></td>
              <td><?= e((string) $l['message']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
