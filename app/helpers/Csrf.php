<?php

declare(strict_types=1);

namespace App\Helpers;

class Csrf
{
    public static function token(string $key): string
    {
        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$key];
    }

    public static function verify(string $key, ?string $token): bool
    {
        if (!$token || empty($_SESSION[$key])) {
            return false;
        }
        return hash_equals($_SESSION[$key], $token);
    }
}
