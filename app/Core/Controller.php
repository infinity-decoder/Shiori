<?php
class Controller
{
    protected function view(string $template, array $data = []): void
    {
        View::render($template, $data);
    }

    protected function redirect(string $path): void
    {
        $app = require BASE_PATH . '/config/app.php';
        header('Location: ' . rtrim($app['base_url'], '/') . $path);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Auth::flash('error', 'Please login to continue.');
            $this->redirect('/login');
        }
    }
}
