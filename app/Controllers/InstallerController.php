<?php

class InstallerController extends Controller
{
    public function handleRequest()
    {
        // 1. Lock Check
        if (file_exists(BASE_PATH . '/config/database.php')) {
            // Further validation could go here (e.g. check if it returns array)
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $step = $_GET['step'] ?? 1;
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $this->handlePost($step);
        } else {
            $this->handleGet($step);
        }
    }

    private function handleGet($step)
    {
        $error = $_SESSION['install_error'] ?? null;
        unset($_SESSION['install_error']);

        switch ($step) {
            case 1:
                View::partial('installer/step1.php', ['error' => $error]);
                break;
            case 2:
                if (empty($_SESSION['install_db'])) { header('Location: ?step=1'); exit; }
                View::partial('installer/step2.php', ['error' => $error]);
                break;
            case 3:
                // Step 3 is now Confirmation (was 4)
                if (empty($_SESSION['install_admin'])) { header('Location: ?step=2'); exit; }
                View::partial('installer/step3.php', ['error' => $error]);
                break;
            default:
                header('Location: ?step=1');
                exit;
        }
    }

    private function handlePost($step)
    {
        switch ($step) {
            case 1:
                $this->processStep1();
                break;
            case 2:
                $this->processStep2();
                break;
            case 3:
                $this->processInstall();
                break;
        }
    }

    private function processStep1()
    {
        $host = $_POST['host'] ?? 'localhost';
        $port = $_POST['port'] ?? '3306';
        $name = $_POST['name'] ?? '';
        $user = $_POST['user'] ?? 'root';
        $pass = $_POST['pass'] ?? '';

        try {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            // Create DB if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");
            
        } catch (PDOException $e) {
            $_SESSION['install_error'] = 'Connection Failed: ' . $e->getMessage();
            header('Location: ?step=1');
            exit;
        }

        $_SESSION['install_db'] = [
            'host' => $host, 'port' => $port, 'name' => $name, 'user' => $user, 'pass' => $pass
        ];
        header('Location: ?step=2');
        exit;
    }

    private function processStep2()
    {
        $user = trim($_POST['admin_user'] ?? '');
        $pass = $_POST['admin_pass'] ?? '';
        $email = trim($_POST['admin_email'] ?? '');
        $name = trim($_POST['admin_name'] ?? '');

        if (empty($user) || empty($pass) || empty($email)) {
            $_SESSION['install_error'] = 'All fields are required.';
            header('Location: ?step=2');
            exit;
        }

        $_SESSION['install_admin'] = [
            'user' => $user, 'pass' => $pass, 'email' => $email, 'name' => $name
        ];
        // Skip Email step, go directly to Confirm (Step 3)
        header('Location: ?step=3');
        exit;
    }

    // processStep3 (Email) Removed

    private function processInstall()
    {
        $db = $_SESSION['install_db'];
        $admin = $_SESSION['install_admin'];
        
        // 1. Write Config
        $configContent = "<?php
return [
    'host'    => '{$db['host']}',
    'port'    => {$db['port']},
    'name'    => '{$db['name']}',
    'user'    => '{$db['user']}',
    'pass'    => '{$db['pass']}',
    'charset' => 'utf8mb4',
];
";
        if (file_put_contents(BASE_PATH . '/config/database.php', $configContent) === false) {
            $_SESSION['install_error'] = 'Could not write config/database.php. Check permissions.';
            header('Location: ?step=3');
            exit;
        }

        // 2. Connect & Run Schema
        try {
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $schemaPath = BASE_PATH . '/database/schema.sql';
            if (file_exists($schemaPath)) {
                $sql = file_get_contents($schemaPath);
                $pdo->exec($sql);
            }

            // 3. Create Super Admin
            $hash = password_hash($admin['pass'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, name, email, created_at) VALUES (?, ?, 'super_admin', ?, ?, NOW())");
            $stmt->execute([$admin['user'], $hash, $admin['name'], $admin['email']]);

            // 4. Save Settings (Empty default for now since we removed the step)
            // or we just skip this and let them configure in Settings > Recovery
            
            // Cleanup Session
            session_destroy();
            
            header('Location: ' . BASE_URL . '/login?installed=1');
            exit;

        } catch (Exception $e) {
            unlink(BASE_PATH . '/config/database.php'); // Allow retry
            $_SESSION['install_error'] = 'Installation failed: ' . $e->getMessage();
            header('Location: ?step=3');
            exit;
        }
    }
}
