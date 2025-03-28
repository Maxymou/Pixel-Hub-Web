<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $prefix = '';
    private array $groupMiddlewares = [];

    public function get(string $path, array $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->prefix . $path,
            'handler' => $handler,
            'middlewares' => array_merge($this->groupMiddlewares, $this->middlewares)
        ];
        return $this;
    }

    public function middleware(array $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function group(array $middlewares, callable $callback): self
    {
        $previousMiddlewares = $this->groupMiddlewares;
        $this->groupMiddlewares = array_merge($this->groupMiddlewares, $middlewares);
        
        $callback($this);
        
        $this->groupMiddlewares = $previousMiddlewares;
        return $this;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $this->executeRoute($route);
                return;
            }
        }

        // Route non trouvée
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error' => 'Route not found']);
    }

    private function matchPath(string $routePath, string $requestPath): bool
    {
        return $routePath === $requestPath;
    }

    private function executeRoute(array $route): void
    {
        // Exécuter les middlewares
        foreach ($route['middlewares'] as $middleware) {
            $instance = new $middleware();
            $instance->handle();
        }

        // Exécuter le handler
        [$controller, $action] = $route['handler'];
        $controllerInstance = new $controller();
        $controllerInstance->$action();
    }
} 