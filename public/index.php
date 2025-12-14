<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

$app = require BASE_PATH . '/config/app.php';
define('BASE_URL', rtrim($app['base_url'], '/'));

// Check for installer
if (!file_exists(BASE_PATH . '/config/database.php')) {
    // Only allow installer routes
    require_once BASE_PATH . '/app/Core/Controller.php';
    require_once BASE_PATH . '/app/Core/View.php';
    require_once BASE_PATH . '/app/Controllers/InstallerController.php';
    
    $uri = $_SERVER['REQUEST_URI'];
    // Simple routing for installer
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new InstallerController())->install();
    } else {
        (new InstallerController())->index();
    }
    exit;
}

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
require_once BASE_PATH . '/app/Services/LookupService.php';
require_once BASE_PATH . '/app/Services/CSVTemplateService.php';
require_once BASE_PATH . '/app/Services/ImportResult.php';
require_once BASE_PATH . '/app/Services/CSVImportService.php';

// Seed default categories and family categories if needed
if (file_exists(BASE_PATH . '/config/database.php')) {
    LookupService::seedDefaults();
}

// Controllers
require_once BASE_PATH . '/app/Controllers/AuthController.php';
require_once BASE_PATH . '/app/Controllers/DashboardController.php';
require_once BASE_PATH . '/app/Controllers/ApiController.php';
require_once BASE_PATH . '/app/Controllers/StudentController.php';
require_once BASE_PATH . '/app/Controllers/ActivityController.php';
require_once BASE_PATH . '/app/Controllers/AdminController.php';
require_once BASE_PATH . '/app/Controllers/FieldController.php';
require_once BASE_PATH . '/app/Controllers/SettingsController.php';
require_once BASE_PATH . '/app/Controllers/LookupController.php';

require_once BASE_PATH . '/app/Models/Field.php';
require_once BASE_PATH . '/app/Models/Session.php';

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
$router->get('/students/import', 'StudentController@import');
$router->get('/students/import-template', 'StudentController@downloadTemplate');
$router->post('/students/import-process', 'StudentController@importProcess'); // File upload
$router->post('/students/import-url', 'StudentController@importFromUrl'); // URL import
$router->get('/students/thumbnail', 'StudentController@thumbnail'); // ?id=#
$router->get('/students/print', 'StudentController@print');    // print-friendly view ?id=#

// Settings & Users
$router->get('/settings', 'SettingsController@index');
$router->post('/settings/users/store', 'SettingsController@storeUser');
$router->post('/settings/users/delete', 'SettingsController@deleteUser');

// Activity Log
$router->get('/activity', 'ActivityController@index');

// Fields Management
$router->get('/fields', 'FieldController@index');
$router->post('/fields/store', 'FieldController@store');
$router->post('/fields/toggle', 'FieldController@toggle');
$router->post('/fields/delete', 'FieldController@delete');

// Lookup Management (Admin only)
$router->get('/lookups', 'LookupController@index');
$router->post('/lookups/classes/store', 'LookupController@storeClass');
$router->post('/lookups/classes/delete', 'LookupController@deleteClass');
$router->post('/lookups/sections/store', 'LookupController@storeSection');
$router->post('/lookups/sections/delete', 'LookupController@deleteSection');
$router->post('/lookups/sessions/store', 'LookupController@storeSession');
$router->post('/lookups/sessions/toggle', 'LookupController@toggleSession');
$router->post('/lookups/categories/store', 'LookupController@storeCategory');
$router->post('/lookups/categories/delete', 'LookupController@deleteCategory');
$router->post('/lookups/familycategories/store', 'LookupController@storeFamilyCategory');
$router->post('/lookups/familycategories/delete', 'LookupController@deleteFamilyCategory');

// Admin utilities
$router->get('/admin/backup', 'AdminController@backup');      // DB dump (admin only)
$router->post('/admin/clear-logs', 'AdminController@clearLogs'); // Clear activity logs

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
