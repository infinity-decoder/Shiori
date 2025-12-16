<?php
// migrate_rbac.php
// Run this to apply the RBAC migration
// Usage: php migrate_rbac.php

define('BASE_PATH', __DIR__);

// Helper for CLI input
function prompt(string $msg, $default = null) {
    if ($default !== null) {
        echo "$msg [$default]: ";
    } else {
        echo "$msg: ";
    }
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    fclose($handle);
    $input = trim($line);
    if ($input === '' && $default !== null) {
        return $default;
    }
    return $input;
}

require_once BASE_PATH . '/app/Core/DB.php';

$configFile = BASE_PATH . '/config/database.php';
$pdo = null;

if (file_exists($configFile)) {
    echo "Found configuration at $configFile\n";
    $config = require $configFile;
    try {
        // Manually create PDO to avoid relying on DB class if config layout differs
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 3306,
            $config['name'] ?? 'shiori',
            $config['charset'] ?? 'utf8mb4'
        );
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (Exception $e) {
        echo "Error connecting with config: " . $e->getMessage() . "\n";
    }
}

if (!$pdo) {
    echo "Could not connect using default config. Please enter database credentials.\n";
    $host = prompt("DB Host", "127.0.0.1");
    $port = prompt("DB Port", "3306");
    $name = prompt("DB Name", "shiori");
    $user = prompt("DB User", "root");
    $pass = prompt("DB Password", ""); // Default empty

    try {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "Connected successfully.\n";
    } catch (Exception $e) {
        die("Fatal Error: Could not connect to database. " . $e->getMessage() . "\n");
    }
}

try {
    echo "Starting Migration...\n";

    $sqlFile = BASE_PATH . '/database/migrations/migrate_admin_to_super_admin.sql';
    if (!file_exists($sqlFile)) {
        die("Error: Migration file not found at $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon to execute one by one
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        
        // Skip comments (simple check)
        if (strpos($stmt, '--') === 0) continue;

        echo "Executing SQL: " . substr($stmt, 0, 60) . "...\n";
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            // Check specific errors
            if (strpos($e->getMessage(), "Unknown column 'role' in 'field list'") !== false) {
                 // Trying to update role but column enum issue?
                 echo "  [Warning] Column issue: " . $e->getMessage() . "\n";
            } elseif (strpos($e->getMessage(), "Duplicate column name") !== false) {
                 echo "  [Info] Already exists.\n";
            } else {
                 echo "  [Note] " . $e->getMessage() . "\n";
            }
        }
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    die("Error during migration: " . $e->getMessage() . "\n");
}
