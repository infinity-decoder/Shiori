<?php

require_once BASE_PATH . '/app/Models/Field.php';

class ManageFieldsController extends Controller
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
        
        $this->view('settings/fields.php', [
            'title' => 'Manage Fields | Shiori',
            'fields' => $fields
        ]);
    }




}
