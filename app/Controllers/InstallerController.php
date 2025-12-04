<?php

class InstallerController extends Controller
{
    public function index()
    {
        if (file_exists(BASE_PATH . '/config/database.php')) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Pass any error messages if they exist in session (simple flash implementation for installer)
        $error = $_SESSION['install_error'] ?? null;
        unset($_SESSION['install_error']);

        View::partial('installer/index.php', ['error' => $error]);
    }

    public function install()
    {
        if (file_exists(BASE_PATH . '/config/database.php')) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $host = $_POST['host'] ?? 'localhost';
        $port = $_POST['port'] ?? '3306';
        $name = $_POST['name'] ?? '';
        $user = $_POST['user'] ?? '';
        $pass = $_POST['pass'] ?? '';
        
        $adminUser = $_POST['admin_user'] ?? 'admin';
        $adminPass = $_POST['admin_pass'] ?? '';

        if (empty($name) || empty($user) || empty($adminUser) || empty($adminPass)) {
            $_SESSION['install_error'] = 'Please fill in all required fields.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        // 1. Test Connection
        try {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            // Create DB if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");
            
        } catch (PDOException $e) {
            $_SESSION['install_error'] = 'Database connection failed: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        // 2. Write config file
        $configContent = "<?php
return [
    'host'    => '$host',
    'port'    => $port,
    'name'    => '$name',
    'user'    => '$user',
    'pass'    => '$pass',
    'charset' => 'utf8mb4',
];
";
        if (file_put_contents(BASE_PATH . '/config/database.php', $configContent) === false) {
            $_SESSION['install_error'] = 'Could not write config/database.php. Check permissions.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        // 3. Run Schema
        try {
            $schemaSql = file_get_contents(BASE_PATH . '/db/schema.sql');
            if ($schemaSql) {
                $pdo->exec($schemaSql);
            }

            // 4. Create Admin User
            // Check if user exists first to avoid duplicate error if re-running
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$adminUser]);
            if ($stmt->fetchColumn() == 0) {
                $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
                $stmt->execute([$adminUser, $hash]);
            }

            // 5. Run Seed (Lookups) if needed
            // For now, we assume schema handles structure. Seed might be optional or we can run it.
            // Let's run seed.sql if it exists
            if (file_exists(BASE_PATH . '/db/seed.sql')) {
                $seedSql = file_get_contents(BASE_PATH . '/db/seed.sql');
                if ($seedSql) {
                    // Split by semicolon to execute multiple statements if PDO doesn't support multi-query well in all drivers
                    // But usually exec handles it if emulation is on. Let's try direct exec.
                    $pdo->exec($seedSql);
                }
            }

        } catch (Exception $e) {
            // If schema fails, we might want to delete the config file so user can try again?
            // unlink(BASE_PATH . '/config/database.php');
            $_SESSION['install_error'] = 'Schema import failed: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        // Success
        header('Location: ' . BASE_URL . '/login?installed=1');
        exit;
    }
}
