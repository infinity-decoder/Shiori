<?php
class AdminController extends Controller
{
    public function backup(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            Auth::flash('error', 'Only super admins can create backups.');
            $this->redirect('/dashboard');
        }

        $cfg = require BASE_PATH . '/config/database.php';
        require_once BASE_PATH . '/app/Services/DatabaseBackupService.php';

        try {
            $filePath = DatabaseBackupService::createBackup($cfg);
            
            if (file_exists($filePath)) {
                // Offer file for download
                header('Content-Type: application/sql');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);

                // Remove file after download
                @unlink($filePath);
                exit;
            } else {
                throw new Exception("Backup file not created.");
            }

        } catch (Exception $e) {
            Auth::flash('error', 'Backup failed: ' . $e->getMessage());
            $this->redirect('/dashboard');
        }
    }

    /**
     * Clear old activity logs (older than 90 days)
     */
    public function clearLogs(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            Auth::flash('error', 'Only super admins can clear activity logs.');
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
