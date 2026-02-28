<section class="card">
  <h2>Управление устройствами</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Устройство</th><th>Тип</th><th>Статус</th><th>Режим</th><th>Действие</th></tr></thead>
      <tbody>
      <?php if (empty($devices)): ?>
        <tr>
          <td colspan="5" class="muted">Для вашего аккаунта пока нет устройств.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($devices as $d): ?>
          <tr>
            <td><?= e($d['name']) ?></td>
            <td><?= e($d['device_type']) ?></td>
            <td><span id="device-status-<?= (int) $d['id'] ?>" class="status <?= $d['status'] === 'on' ? 'status-ok' : 'status-warn' ?>"><?= $d['status'] === 'on' ? 'Включено' : 'Выключено' ?></span></td>
            <td><?= (int) $d['is_auto'] === 1 ? 'Авто' : 'Ручной' ?></td>
            <td>
              <button
                data-device-toggle
                data-id="<?= (int) $d['id'] ?>"
                data-csrf="<?= e($csrfToken) ?>"
                class="btn-secondary"
              >
                <?= $d['status'] === 'on' ? 'Выключить' : 'Включить' ?>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
