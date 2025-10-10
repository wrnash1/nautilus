<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    
    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    private function addRoute(string $method, string $path, $handler, array $middleware): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $uri, $params)) {
                $this->executeMiddleware($route['middleware']);
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }
        
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
    
    private function matchPath(string $pattern, string $uri, &$params): bool
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }
        
        return false;
    }
    
    private function executeMiddleware(array $middleware): void
    {
        foreach ($middleware as $mw) {
            $instance = new $mw();
            $instance->handle();
        }
    }
    
    private function executeHandler($handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            [$controller, $method] = explode('@', $handler);
            $controller = "App\\Controllers\\{$controller}";
            $instance = new $controller();
            call_user_func_array([$instance, $method], $params);
        }
    }
}
