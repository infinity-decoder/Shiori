<?php

require_once BASE_PATH . '/app/Services/ActivityLogger.php';

class ActivityController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            Auth::flash('error', 'Unauthorized access.');
            $this->redirect('/dashboard');
        }

        $logs = ActivityLogger::getLatest(100);
        
        $this->view('activity/index.php', [
            'title' => 'Activity Log | Shiori',
        ]);
    }

    public function clear(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            Auth::flash('error', 'Only Super Admin can clear logs.');
            $this->redirect('/activity');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
                Auth::flash('error', 'Invalid CSRF token.');
                $this->redirect('/activity');
            }

            require_once BASE_PATH . '/app/Services/DatabaseHelper.php';
            DatabaseHelper::ensureActivityLogsTable();

            $pdo = DB::get();
            $pdo->exec("TRUNCATE TABLE activity_logs");
            
            // Log that logs were cleared (ironic but necessary)
            ActivityLogger::log('delete', 'activity_logs', 0, ['details' => 'Cleared all activity logs']);

            Auth::flash('success', 'Activity logs cleared successfully.');
        }

        $this->redirect('/activity');
    }
}
