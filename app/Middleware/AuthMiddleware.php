<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Auth;

class AuthMiddleware
{
    public static function ensure(): bool
    {
        return Auth::check();
    }
}
