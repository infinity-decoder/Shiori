<?php

require_once BASE_PATH . '/app/Services/DatabaseHelper.php';
require_once BASE_PATH . '/app/Services/ActivityLogger.php';

class SettingsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        // Allow Admin or Super Admin to view settings, but specific tabs might be restricted in View?
        // User requested: Manage Users, Setup Recovery, Activity Log
        // "Manage Users" usually requires Admin. "Recovery" Super Admin.
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }

        // Ensure tables exist to prevent crashing
        DatabaseHelper::ensureSettingsTable();
        
        $this->view('settings/index.php', [
            'title' => 'Settings | Shiori',
            'email' => $this->getEmailSettings(),
            'logs' => ActivityLogger::getLatest(20), // Preview logs
            'users' => [] // Users fetched via Ajax or separate controller usually, but if simple...
        ]);
    }

    private function getEmailSettings(): array
    {
        $pdo = DB::get();
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%' OR setting_key LIKE 'mail_%'");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            return $settings;
        } catch (Exception $e) {
            return []; // Fail safe
        }
    }

    public function storeEmail(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            Auth::flash('error', 'Only Super Admin can change recovery settings.');
            $this->redirect('/settings');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid Security Token');
            $this->redirect('/settings');
        }

        DatabaseHelper::ensureSettingsTable();

        $data = [
            'smtp_host' => $_POST['smtp_host'] ?? '',
            'smtp_port' => $_POST['smtp_port'] ?? '',
            'smtp_user' => $_POST['smtp_user'] ?? '',
            'smtp_pass' => $_POST['smtp_pass'] ?? '',
            'mail_from_name' => $_POST['from_name'] ?? 'Shiori Admin',
        ];

        $pdo = DB::get();
        // Assuming settings table exists now
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($data as $key => $val) {
            $stmt->execute([$key, $val]);
        }
        
        Auth::flash('success', 'Email settings saved.');
        $this->redirect('/settings');
    }
}
