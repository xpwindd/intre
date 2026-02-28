<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);
$config = require $root . '/config/config.php';

date_default_timezone_set('Europe/Moscow');

session_name($config['security']['session_name']);
session_set_cookie_params([
    'httponly' => true,
    'secure' => false,
    'samesite' => 'Lax',
]);
session_start();

spl_autoload_register(function (string $class) use ($root): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $path = $root . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

require_once $root . '/app/helpers/functions.php';

set_exception_handler(function (Throwable $e) use ($root): void {
    @file_put_contents(
        $root . '/logs/app.log',
        sprintf("[%s] EXCEPTION: %s in %s:%d\n", date('c'), $e->getMessage(), $e->getFile(), $e->getLine()),
        FILE_APPEND
    );
    http_response_code(500);
    echo 'Внутренняя ошибка сервера.';
});

set_error_handler(function (int $severity, string $message, string $file, int $line) use ($root): bool {
    @file_put_contents(
        $root . '/logs/app.log',
        sprintf("[%s] ERROR: %s in %s:%d\n", date('c'), $message, $file, $line),
        FILE_APPEND
    );
    return false;
});

$routes = require $root . '/routes/web.php';
$router = new App\Helpers\Router($config, $routes);
$router->dispatch();
