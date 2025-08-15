<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

$app = require BASE_PATH . '/config/app.php';

if (!headers_sent()) {
    if (!empty($app['debug'])) {
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', '0');
        error_reporting(0);
    }

    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $isSecure ? '1' : '0');
        ini_set('session.cookie_samesite', 'Lax');
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ---------- Core
require_once BASE_PATH . '/app/Core/DB.php';
require_once BASE_PATH . '/app/Core/Router.php';
require_once BASE_PATH . '/app/Core/Controller.php';
require_once BASE_PATH . '/app/Core/View.php';
require_once BASE_PATH . '/app/Core/CSRF.php';
require_once BASE_PATH . '/app/Core/Auth.php';

// Models
require_once BASE_PATH . '/app/Models/User.php';
require_once BASE_PATH . '/app/Models/Student.php';
require_once BASE_PATH . '/app/Models/Lookup.php';

// Services
require_once BASE_PATH . '/app/Services/Validator.php';
require_once BASE_PATH . '/app/Services/ImageService.php';
require_once BASE_PATH . '/app/Services/ActivityLog.php';

// Controllers
require_once BASE_PATH . '/app/Controllers/AuthController.php';
require_once BASE_PATH . '/app/Controllers/DashboardController.php';
require_once BASE_PATH . '/app/Controllers/ApiController.php';
require_once BASE_PATH . '/app/Controllers/StudentController.php';
require_once BASE_PATH . '/app/Controllers/AdminController.php';

// ---------- Routing ----------
$router = new Router($app['base_url']);

$router->get('/', function () use ($app) {
    if (Auth::check()) {
        header('Location: ' . $app['base_url'] . '/dashboard');
    } else {
        header('Location: ' . $app['base_url'] . '/login');
    }
    exit;
});

$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');

$router->get('/dashboard', 'DashboardController@index');

// API
$router->get('/api/stats', 'ApiController@stats');
$router->get('/api/search', 'ApiController@search');

// Student CRUD + extras
$router->get('/students', 'StudentController@index');
$router->get('/students/create', 'StudentController@create');
$router->post('/students', 'StudentController@store');
$router->get('/students/show', 'StudentController@show');      // ?id=#
$router->get('/students/edit', 'StudentController@edit');      // ?id=#
$router->post('/students/update', 'StudentController@update'); // ?id=#
$router->post('/students/delete', 'StudentController@destroy'); // ?id=#
$router->get('/students/export', 'StudentController@export');  // CSV export
$router->get('/students/print', 'StudentController@print');    // print-friendly view ?id=#

// Admin utilities
$router->get('/admin/backup', 'AdminController@backup');      // DB dump (admin only)

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
