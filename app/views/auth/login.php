<div class="card" style="max-width: 480px; margin: 0 auto;">
  <h2>Вход в систему</h2>
  <form method="post" action="/?route=login">
    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Пароль</label>
    <input type="password" name="password" required>
    <button type="submit">Войти</button>
  </form>
  <p><a href="/?route=forgot">Забыли пароль?</a></p>
</div>
