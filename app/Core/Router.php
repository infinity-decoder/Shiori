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
        $trimmed = substr($uriPath, strlen($this->basePath));
        $trimmed = '/' . ltrim($trimmed, '/');
        return $this->normalize($trimmed);
    }

    public function dispatch(string $method, string $requestUri): void
    {
        $method = strtoupper($method);
        $path   = $this->currentPath($requestUri);

        $handler = $this->routes[$method][$path] ?? null;

        try {
            if ($handler === null) {
                http_response_code(404);
                View::render('errors/404.php', ['title' => '404 Not Found']);
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
        } catch (Throwable $e) {
            // log the error if storage/logs exists
            try {
                $logDir = BASE_PATH . '/storage/logs';
                if (is_dir($logDir)) {
                    $msg = '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
                    @file_put_contents($logDir . '/error.log', $msg, FILE_APPEND);
                }
            } catch (Throwable $_) {
                // ignore logging errors
            }

            http_response_code(500);
            // In debug mode display exception message on the 500 page
            $app = require BASE_PATH . '/config/app.php';
            $debug = !empty($app['debug']);
            View::render('errors/500.php', [
                'title' => 'Server error',
                'error' => $debug ? $e->getMessage() : null,
            ]);
        }
    }
}
