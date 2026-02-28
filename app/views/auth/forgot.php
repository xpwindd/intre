<div class="card" style="max-width: 520px; margin: 0 auto;">
  <h2>Восстановление пароля</h2>
  <form method="post" action="/?route=forgot">
    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
    <label>Email</label>
    <input type="email" name="email" required>
    <button type="submit">Отправить ссылку</button>
  </form>
</div>
