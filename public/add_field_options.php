<?php
// public/add_field_options.php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/DB.php';

try {
    $pdo = DB::get();
    echo "Connected to database.<br>";

    // Add options column
    try {
        $pdo->exec("ALTER TABLE `fields` ADD COLUMN `options` TEXT DEFAULT NULL AFTER `type`");
        echo "Added 'options' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'options' might already exist.<br>";
    }

    echo "Fields table update completed.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
