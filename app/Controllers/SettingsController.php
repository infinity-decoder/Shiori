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
            'fields' => $fields
        ]);
    }


}
