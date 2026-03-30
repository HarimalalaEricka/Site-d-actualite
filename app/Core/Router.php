<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /**
     * @var array<int, array{method:string, pattern:string, handler:callable}>
     */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $requestMethod = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $matches = [];
            if (preg_match($route['pattern'], $uri, $matches) !== 1) {
                continue;
            }

            array_shift($matches);
            call_user_func_array($route['handler'], $matches);
            return;
        }

        http_response_code(404);
        echo 'Page introuvable';
    }
}
