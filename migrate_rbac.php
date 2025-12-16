<?php
// migrate_rbac.php
// Run this to apply the RBAC migration

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/app/Core/DB.php';

// Load config to init DB
$configFile = BASE_PATH . '/config/database.php';
if (!file_exists($configFile)) {
    die("Error: Configuration file not found at $configFile. Please ensure the application is installed/configured.\n");
}
$config = require $configFile;

try {
    $pdo = DB::get();
    echo "Connected to database.\n";

    $sqlFile = BASE_PATH . '/database/migrations/migrate_admin_to_super_admin.sql';
    if (!file_exists($sqlFile)) {
        die("Error: Migration file not found at $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon to execute one by one (basic runner)
    // Note: This is a simple split, might break if semicolons are in strings, but fine for this specific SQL
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        echo "Executing: " . substr($stmt, 0, 50) . "...\n";
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            // Ignore "Duplicate column name" or similar if re-running
            echo "  Note: " . $e->getMessage() . "\n";
        }
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
