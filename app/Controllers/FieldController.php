<?php

class FieldController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            Auth::flash('error', 'Unauthorized access.');
            $this->redirect('/dashboard');
        }

        $fields = Field::getAll();
        $this->view('fields/index.php', [
            'title' => 'Manage Fields | Shiori',
            'fields' => $fields
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $label = trim($_POST['label'] ?? '');
            $type = $_POST['type'] ?? 'text';
            
            if (empty($label)) {
                Auth::flash('error', 'Label is required.');
                $this->redirect('/settings');
            }

            // Generate name from label
            $name = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $label));
            $name = 'custom_' . $name . '_' . time();

            Field::create([
                'name' => $name,
                'label' => $label,
                'type' => $type,
                'is_custom' => 1,
                'is_active' => 1,
                'order_index' => 99
            ]);

            Auth::flash('success', 'Field created.');
            $this->redirect('/settings');
        }
    }

    public function toggle(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Field::toggle($id);
            Auth::flash('success', 'Field status updated.');
        }
        $this->redirect('/settings');
    }

    public function delete(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Field::delete($id);
            Auth::flash('success', 'Field deleted.');
        }
        $this->redirect('/settings');
    }
}
