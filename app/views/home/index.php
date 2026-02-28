<section class="hero">
  <h1>Умный сад и дневник роста</h1>
  <p class="muted">Единая платформа для управления поливом, освещением, мониторингом датчиков и журналированием ухода за растениями.</p>
  <?php if (!empty($currentUser)): ?>
    <div class="kpi">
      <div class="item"><div class="muted">Растения</div><div class="val"><?= (int) $stats['plants'] ?></div></div>
      <div class="item"><div class="muted">Зоны</div><div class="val"><?= (int) $stats['zones'] ?></div></div>
      <div class="item"><div class="muted">Новые уведомления</div><div class="val"><?= (int) $stats['notifications'] ?></div></div>
    </div>
  <?php else: ?>
    <p><a class="btn" href="/?route=register">Создать аккаунт</a></p>
  <?php endif; ?>
</section>
