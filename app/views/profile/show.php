<div class="card" style="max-width: 620px;">
  <h2>Профиль пользователя</h2>
  <p class="muted">Дата регистрации: <?= e((string) ($user['created_at'] ?? '')) ?></p>
  <form method="post" action="/?route=profile">
    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
    <label>Имя</label>
    <input type="text" name="name" value="<?= e((string) ($user['name'] ?? '')) ?>" required>
    <label>Email</label>
    <input type="email" value="<?= e((string) ($user['email'] ?? '')) ?>" disabled>
    <button type="submit">Обновить</button>
  </form>
</div>
