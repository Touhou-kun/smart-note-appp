<?php

declare(strict_types=1);

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = $this->currentPath();
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo '404 - Page not found';
            return;
        }

        [$class, $action] = $handler;
        $controller = new $class();
        $controller->$action();
    }

    private function currentPath(): string
    {
        $path = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        $path = trim((string)$path, '/');

        if ($scriptDir !== '' && str_starts_with($path, $scriptDir)) {
            $path = trim(substr($path, strlen($scriptDir)), '/');
        }

        return $this->normalize($path === '' ? '/' : $path);
    }

    private function normalize(string $path): string
    {
        $path = trim($path, '/');
        return $path === '' ? '/' : $path;
    }
}

