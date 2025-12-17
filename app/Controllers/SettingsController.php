<?php

require_once BASE_PATH . '/app/Models/Field.php';

class SettingsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            Auth::flash('error', 'Unauthorized access.');
            $this->redirect('/dashboard');
        }

        // Fetch fields
        $fields = Field::getAll(false); // get all, including inactive
        
        $this->view('settings/index.php', [
            'title' => 'Settings | Shiori',
            'fields' => $fields,
            'email' => $this->getEmailSettings()
        ]);
    }

    private function getEmailSettings(): array
    {
        $pdo = DB::get();
        // Fetch all settings starting with smtp_ or mail_
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%' OR setting_key LIKE 'mail_%'");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function storeEmail(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            $this->redirect('/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
        }

        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid CSRF token.');
            $this->redirect('/settings');
        }

        $data = [
            'smtp_host' => $_POST['smtp_host'] ?? '',
            'smtp_port' => $_POST['smtp_port'] ?? '',
            'smtp_user' => $_POST['smtp_user'] ?? '',
            'smtp_pass' => $_POST['smtp_pass'] ?? '',
            'mail_from_name' => $_POST['from_name'] ?? 'Shiori Admin',
        ];

        $pdo = DB::get();
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        try {
            foreach ($data as $key => $val) {
                // If password is empty/masked, don't overwrite if not changed? 
                // Simple version: overwrite. Real version: handle password masking.
                // Assuming admin wants to update if they type it.
                $stmt->execute([$key, $val]);
            }
            Auth::flash('success', 'Email settings updated.');
        } catch (Exception $e) {
            Auth::flash('error', 'Failed to save settings: ' . $e->getMessage());
        }

        $this->redirect('/settings');
    }


}
