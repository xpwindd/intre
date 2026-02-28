<?php

declare(strict_types=1);

namespace App\Helpers;

class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewPath = dirname(__DIR__, 2) . '/app/views/' . $view . '.php';
        if (!is_file($viewPath)) {
            http_response_code(500);
            echo 'Шаблон не найден: ' . e($view);
            return;
        }

        extract($data, EXTR_SKIP);
        require dirname(__DIR__, 2) . '/app/views/layouts/header.php';
        require $viewPath;
        require dirname(__DIR__, 2) . '/app/views/layouts/footer.php';
    }
}
