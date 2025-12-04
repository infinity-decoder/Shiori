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
            'logs' => $logs
        ]);
    }
}
