<?php

declare(strict_types=1);

namespace App\Helpers;

class Auth
{
    public static function id(): ?int
    {
        return isset($_SESSION['user']) ? (int) $_SESSION['user']['id'] : null;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role_slug'] ?? '') === 'admin';
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role_slug' => $user['role_slug'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
