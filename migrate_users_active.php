<?php
// migrate_users_active.php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/app/Core/DB.php';

echo "Applying 'is_active' column to users table...\n";

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
    $pass = prompt("DB Password", ""); 

    try {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "Connected successfully.\n";
    } catch (Exception $e) {
        die("Fatal Error: Could not connect to database. " . $e->getMessage() . "\n");
    }
}

try {
    // Check if column exists to avoid error
    $check = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($check->fetch()) {
        die("Column 'is_active' already exists. Migration skipped.\n");
    }

    $sqlFile = BASE_PATH . '/database/migrations/add_is_active_to_users.sql';
    if (!file_exists($sqlFile)) {
        die("Migration file not found: $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    
    // Simple split
    $parts = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($parts as $Part) {
        if ($Part) {
            echo "Running: " . substr($Part, 0, 50) . "...\n";
            $pdo->exec($Part);
        }
    }
    
    echo "Migration successful. Users table updated.\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
