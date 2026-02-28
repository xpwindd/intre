<div class="card" style="max-width: 520px; margin: 0 auto;">
  <h2>Новый пароль</h2>
  <form method="post" action="/?route=reset">
    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
    <label>Новый пароль</label>
    <input type="password" name="password" minlength="6" required>
    <button type="submit">Сохранить пароль</button>
  </form>
</div>
