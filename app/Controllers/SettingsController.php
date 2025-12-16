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
        
        // Fetch users (simple query for now, ideally move to User model)
        $pdo = DB::get();
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll();

        $this->view('settings/index.php', [
            'title' => 'Settings | Shiori',
            'fields' => $fields,
            'users' => $users
        ]);
    }

    public function storeUser(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            $this->redirect('/dashboard');
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'viewer';

        if (empty($username) || empty($password) || empty($name)) {
            Auth::flash('error', 'Name, Username and Password are required.');
            $this->redirect('/settings');
        }

        $pdo = DB::get();
        // Check duplicate
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            Auth::flash('error', 'Username or Email already exists.');
            $this->redirect('/settings');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, username, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$name, $email, $username, $hash, $role])) {
            Auth::flash('success', 'User created successfully.');
        } else {
            Auth::flash('error', 'Failed to create user.');
        }
        $this->redirect('/settings');
    }

    public function deleteUser(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            $this->redirect('/dashboard');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id === Auth::user()['id']) {
            Auth::flash('error', 'Cannot delete yourself.');
            $this->redirect('/settings');
        }

        $pdo = DB::get();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            Auth::flash('success', 'User deleted.');
        } else {
            Auth::flash('error', 'Failed to delete user.');
        }
        $this->redirect('/settings');
    }
}
