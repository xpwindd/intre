<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Database;
use App\Helpers\View;
use PDO;

class BaseController
{
    protected array $config;
    protected PDO $db;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->db = Database::connection($config);
    }

    protected function render(string $view, array $data = []): void
    {
        $defaults = [
            'appName' => $this->config['app']['name'],
            'currentUser' => Auth::user(),
            'csrfToken' => Csrf::token($this->config['security']['csrf_key']),
            'pageTitle' => $this->config['app']['name'],
            'metaDescription' => 'Платформа управления умным садом и дневник роста растений.',
        ];
        View::render($view, array_merge($defaults, $data));
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            header('Location: /?route=login');
            exit;
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Доступ запрещен.';
            exit;
        }
    }

    protected function redirect(string $route): void
    {
        header('Location: /?route=' . ltrim($route, '/'));
        exit;
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!Csrf::verify($this->config['security']['csrf_key'], is_string($token) ? $token : null)) {
            http_response_code(419);
            echo 'Ошибка CSRF-токена.';
            exit;
        }
    }
}
