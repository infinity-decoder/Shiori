<?php
// public/add_last_login.php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/DB.php';

try {
    $pdo = DB::get();
    echo "Connected to database.<br>";

    // Add last_login column
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `last_login` DATETIME DEFAULT NULL AFTER `email`");
        echo "Added 'last_login' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'last_login' might already exist.<br>";
    }

    echo "Users table update completed.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
