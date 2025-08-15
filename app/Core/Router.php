<?php
class Router
{
    private array $routes = [
        'GET'  => [],
        'POST' => [],
    ];
    private string $baseUrl;
    private string $basePath;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl  = rtrim($baseUrl, '/');
        $this->basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    }

    public function get(string $path, $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }

    private function currentPath(string $requestUri): string
    {
        $uriPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        // Trim the base script path (e.g., /Shiori/public)
        $trimmed = substr($uriPath, strlen($this->basePath));
        $trimmed = '/' . ltrim($trimmed, '/');
        return $this->normalize($trimmed);
    }

    public function dispatch(string $method, string $requestUri): void
    {
        $method = strtoupper($method);
        $path   = $this->currentPath($requestUri);

        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>No route for {$method} {$path}</p>";
            return;
        }

        if (is_callable($handler)) {
            $handler();
            return;
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controller, $action] = explode('@', $handler, 2);
            if (!class_exists($controller)) {
                throw new Exception("Controller $controller not found");
            }
            $instance = new $controller();
            if (!method_exists($instance, $action)) {
                throw new Exception("Action $controller::$action not found");
            }
            $instance->$action();
            return;
        }

        throw new Exception('Invalid route handler type');
    }
}
