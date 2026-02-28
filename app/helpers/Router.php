<?php

declare(strict_types=1);

namespace App\Helpers;

class Router
{
    private array $config;
    private array $routes;

    public function __construct(array $config, array $routes)
    {
        $this->config = $config;
        $this->routes = $routes;
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $route = trim((string) ($_GET['route'] ?? ''), '/');
        if ($route === '') {
            $uriPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
            $scriptDir = trim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
            $clean = trim((string) $uriPath, '/');
            if ($scriptDir !== '' && strpos($clean, $scriptDir) === 0) {
                $clean = ltrim(substr($clean, strlen($scriptDir)), '/');
            }
            $route = $clean === '' || $clean === 'index.php' ? 'home' : $clean;
        }

        foreach ($this->routes as $item) {
            if ($method !== $item['method'] || $route !== $item['path']) {
                continue;
            }

            [$class, $action] = $item['handler'];
            $controller = new $class($this->config);
            $controller->{$action}();
            return;
        }

        http_response_code(404);
        $controller = new \App\Controllers\HomeController($this->config);
        $controller->notFound();
    }
}
