<?php
define('BASE_PATH', __DIR__);
require BASE_PATH . '/app/Core/DB.php';

try {
    $pdo = DB::get();
    $sql = file_get_contents(BASE_PATH . '/db/migrations/001_dynamic_fields.sql');
    if ($sql) {
        $pdo->exec($sql);
        echo "Migration 001_dynamic_fields executed successfully.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
