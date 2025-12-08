<?php
class AdminController extends Controller
{
    public function backup(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            Auth::flash('error', 'Only admins can create backups.');
            $this->redirect('/dashboard');
        }

        $cfg = require BASE_PATH . '/config/database.php';
        $filename = sprintf('FGSS_backup_%s.sql', date('Ymd_His'));
        $backupDir = BASE_PATH . '/storage/backups';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Check exec availability
        $disabled = explode(',', ini_get('disable_functions') ?: '');
        $disabled = array_map('trim', $disabled);
        if (in_array('exec', $disabled, true) && in_array('shell_exec', $disabled, true)) {
            Auth::flash('error', 'Server does not allow shell execution (exec/shell_exec disabled). Cannot create backup via web.');
            $this->redirect('/dashboard');
        }

        // Build command (will work on typical Laragon/Windows if mysqldump in PATH).
        // WARNING: password appears in process list. This is for local/dev use only.
        $host = $cfg['host'] ?? '127.0.0.1';
        $port = $cfg['port'] ?? 3306;
        $user = $cfg['user'] ?? 'root';
        $pass = $cfg['pass'] ?? '';
        $db   = $cfg['name'];

        // Use escapeshellarg for safety
        // Use escapeshellarg for safety
        // Try common paths
        $candidates = [
            'C:/xampp/mysql/bin/mysqldump.exe',
            'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe', // Example Laragon path
            'C:/Program Files/Ampps/mysql/bin/mysqldump.exe',
        ];
        
        // Dynamic Laragon check
        if (is_dir('C:/laragon/bin/mysql')) {
            $dirs = glob('C:/laragon/bin/mysql/*', GLOB_ONLYDIR);
            if ($dirs) {
                foreach ($dirs as $d) {
                    $candidates[] = $d . '/bin/mysqldump.exe';
                }
            }
        }

        $mysqldumpPath = 'mysqldump'; // Default fallback
        foreach ($candidates as $c) {
            if (file_exists($c)) {
                $mysqldumpPath = $c;
                break;
            }
        }

        if ($mysqldumpPath === 'mysqldump') {
             // Try to find it via 'where' command if on Windows
             $output = [];
             @exec('where mysqldump', $output);
             if (!empty($output[0])) {
                 $mysqldumpPath = trim($output[0]);
             }
        }

        $cmdParts = [
            '"' . $mysqldumpPath . '"',
            '--host=' . escapeshellarg($host),
            '--port=' . (int)$port,
            '--user=' . escapeshellarg($user),
        ];
        // Add password carefully (mysqldump needs --password=VALUE)
        if ($pass !== '') {
            $cmdParts[] = '--password=' . escapeshellarg($pass);
        }
        $cmdParts[] = escapeshellarg($db);
        // redirect output to file
        $fullCmd = implode(' ', $cmdParts) . ' > ' . escapeshellarg($filePath);

        // Execute
        $ret = null;
        @exec($fullCmd, $output, $ret);

        if ($ret !== 0 || !is_file($filePath)) {
            Auth::flash('error', 'Backup failed. Ensure mysqldump is available and exec() is allowed.');
            $this->redirect('/dashboard');
        }

        // Offer file for download
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);

        // Optionally remove file after download (safe for local)
        @unlink($filePath);

        exit;
    }

    /**
     * Clear old activity logs (older than 90 days)
     */
    public function clearLogs(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            Auth::flash('error', 'Only admins can clear activity logs.');
            $this->redirect('/dashboard');
        }

        $pdo = DB::get();
        
        try {
            // Delete logs older than 90 days
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
            $stmt->execute();
            $deletedCount = $stmt->rowCount();
            
            Auth::flash('success', "Cleared $deletedCount old activity logs (older than 90 days).");
        } catch (PDOException $e) {
            Auth::flash('error', 'Failed to clear logs: ' . $e->getMessage());
        }
        
        $this->redirect('/activity');
    }
}
