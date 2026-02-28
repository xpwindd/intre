<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($metaDescription ?? '') ?>">
  <meta property="og:title" content="<?= e($pageTitle ?? '') ?>">
  <meta property="og:description" content="<?= e($metaDescription ?? '') ?>">
  <meta property="og:type" content="website">
  <title><?= e($pageTitle ?? 'Smart Garden') ?> | <?= e($appName ?? 'Smart Garden') ?></title>
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="topbar">
  <div class="container topbar-inner">
    <a class="brand" href="/?route=home">Smart Garden</a>
    <?php
      $currentRoute = trim((string) ($_GET['route'] ?? ''), '/');
      if ($currentRoute === '') {
          $uriPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
          $scriptDir = trim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
          $clean = trim((string) $uriPath, '/');
          if ($scriptDir !== '' && strpos($clean, $scriptDir) === 0) {
              $clean = ltrim(substr($clean, strlen($scriptDir)), '/');
          }
          $currentRoute = $clean === '' || $clean === 'index.php' ? 'home' : $clean;
      }
      $currentRouteBase = explode('/', $currentRoute)[0];
      $isActiveRoute = static function (array $routes) use ($currentRoute, $currentRouteBase): string {
          foreach ($routes as $routeName) {
              if ($currentRoute === $routeName || $currentRouteBase === $routeName) {
                  return ' class="is-active"';
              }
          }
          return '';
      };
    ?>
    <nav class="nav">
      <a<?= $isActiveRoute(['home']) ?> href="/?route=home">Главная</a>
      <?php if (!empty($currentUser)): ?>
        <a<?= $isActiveRoute(['dashboard']) ?> href="/?route=dashboard">Кабинет</a>
        <a<?= $isActiveRoute(['plants']) ?> href="/?route=plants">Растения</a>
        <a<?= $isActiveRoute(['diary']) ?> href="/?route=diary">Дневник</a>
        <a<?= $isActiveRoute(['devices']) ?> href="/?route=devices">Устройства</a>
        <a<?= $isActiveRoute(['status']) ?> href="/?route=status">Статус</a>
        <a<?= $isActiveRoute(['sensors']) ?> href="/?route=sensors">Датчики</a>
        <a<?= $isActiveRoute(['automation']) ?> href="/?route=automation">Расписания</a>
        <a<?= $isActiveRoute(['notifications']) ?> href="/?route=notifications">Уведомления</a>
        <?php if (($currentUser['role_slug'] ?? '') === 'admin'): ?>
          <a<?= $isActiveRoute(['admin']) ?> href="/?route=admin">Админ</a>
        <?php endif; ?>
        <a<?= $isActiveRoute(['profile']) ?> href="/?route=profile"><?= e($currentUser['name']) ?></a>
        <a href="/?route=logout">Выход</a>
      <?php else: ?>
        <a<?= $isActiveRoute(['login', 'forgot', 'reset']) ?> href="/?route=login">Вход</a>
        <a<?= $isActiveRoute(['register']) ?> href="/?route=register">Регистрация</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="main">
  <div class="container">
    <?php if (!empty($_SESSION['flash_success'])): ?>
      <div class="flash flash-success"><?= e($_SESSION['flash_success']) ?></div>
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="flash flash-error"><?= e($_SESSION['flash_error']) ?></div>
      <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
