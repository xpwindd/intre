<div class="card" style="max-width: 540px; margin: 0 auto;">
  <h2>Регистрация</h2>
  <form method="post" action="/?route=register">
    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
    <label>Имя</label>
    <input type="text" name="name" required>
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Пароль (минимум 6 символов)</label>
    <input type="password" name="password" minlength="6" required>
    <button type="submit">Создать аккаунт</button>
  </form>
</div>
