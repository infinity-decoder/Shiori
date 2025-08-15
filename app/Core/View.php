<?php
class View
{
    public static function render(string $template, array $data = []): void
    {
        $app     = require BASE_PATH . '/config/app.php';
        $baseUrl = rtrim($app['base_url'], '/');

        $viewFile = BASE_PATH . '/app/Views/' . ltrim($template, '/');
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$template}");
        }

        // Variables available to layout & view
        $title = $data['title'] ?? $app['name'];

        // Extract $data for use in views (e.g., $user)
        extract($data);

        // Make $viewFile, $baseUrl available to layout
        include BASE_PATH . '/app/Views/layouts/main.php';
    }
}
