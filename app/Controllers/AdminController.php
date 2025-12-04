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
        $mysqldumpPath = 'c:\\Program Files\\Ampps\\mysql\\bin\\mysqldump.exe';
        if (!file_exists($mysqldumpPath)) {
            $mysqldumpPath = 'mysqldump'; // Fallback to PATH
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
}
