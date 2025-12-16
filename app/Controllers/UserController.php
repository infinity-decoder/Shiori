<?php
class UserController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            Auth::flash('error', 'Unauthorized access.');
            $this->redirect('/dashboard');
        }

        require_once BASE_PATH . '/app/Models/User.php';
        $users = User::getAll();

        $this->view('users/index.php', [
            'title' => 'Manage Users | Shiori',
            'users' => $users
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('users/form.php', [
            'title' => 'Add User | Shiori',
            'user' => null,
            'mode' => 'create'
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/users/create');
        }

        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'viewer';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name) || empty($username) || empty($password)) {
            Auth::flash('error', 'Name, Username, and Password are required.');
            Auth::setOldInput($_POST);
            $this->redirect('/users/create');
        }

        // Validate Role
        $validRoles = ['super_admin', 'admin', 'staff', 'viewer'];
        if (!in_array($role, $validRoles)) {
            Auth::flash('error', 'Invalid role selected.');
            $this->redirect('/users/create');
        }

        require_once BASE_PATH . '/app/Models/User.php';
        if (User::exists($username, $email)) {
            Auth::flash('error', 'Username or Email already exists.');
            Auth::setOldInput($_POST);
            $this->redirect('/users/create');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            User::create([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password_hash' => $hash,
                'role' => $role,
                'is_active' => $isActive
            ]);

            // Audit
            ActivityLogger::log('create', 'user', 0, [
                'username' => $username,
                'role' => $role,
                'by' => Auth::user()['username']
            ]);

            Auth::flash('success', 'User created successfully.');
            Auth::flushOldInput();
            $this->redirect('/users');
        } catch (Exception $e) {
            Auth::flash('error', 'Failed to create user: ' . $e->getMessage());
            $this->redirect('/users/create');
        }
    }

    public function edit(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            $this->redirect('/dashboard');
        }

        $id = (int)($_GET['id'] ?? 0);
        require_once BASE_PATH . '/app/Models/User.php';
        $user = User::get($id);

        if (!$user) {
            Auth::flash('error', 'User not found.');
            $this->redirect('/users');
        }

        $this->view('users/form.php', [
            'title' => 'Edit User | Shiori',
            'user' => $user,
            'mode' => 'edit'
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            $this->redirect('/dashboard');
        }

        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/users');
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'viewer';
        $isActive = isset($_POST['is_active']) ? 1 : 0; // Checkbox not present = 0

        require_once BASE_PATH . '/app/Models/User.php';
        
        // Self-protection: Cannot deactivate or demote self
        if ($id === Auth::user()['id']) {
            // Force active and super_admin if editing self
            $isActive = 1;
            if ($role !== 'super_admin') {
                Auth::flash('error', 'You cannot demote yourself.');
                $role = 'super_admin';
            }
        }

        if (empty($name) || empty($username)) {
            Auth::flash('error', 'Name and Username are required.');
            $this->redirect('/users/edit?id=' . $id);
        }

        if (User::exists($username, $email, $id)) {
            Auth::flash('error', 'Username or Email already taken by another user.');
            $this->redirect('/users/edit?id=' . $id);
        }

        // Prepare update data
        $updateData = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'is_active' => $isActive
        ];

        // Password change logic
        if (!empty($_POST['password'])) {
            $updateData['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        try {
            User::update($id, $updateData);

            ActivityLogger::log('update', 'user', $id, [
                'username' => $username,
                'role' => $role,
                'active' => $isActive
            ]);

            Auth::flash('success', 'User updated successfully.');
            $this->redirect('/users');
        } catch (Exception $e) {
            Auth::flash('error', 'Update failed: ' . $e->getMessage());
            $this->redirect('/users/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            $this->redirect('/dashboard');
        }

        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid token.');
            $this->redirect('/users');
        }

        $id = (int)($_POST['id'] ?? 0);
        
        // Self-protection
        if ($id === Auth::user()['id']) {
            Auth::flash('error', 'You cannot delete yourself.');
            $this->redirect('/users');
        }

        // Optional: Protect other super admins? 
        // Requirement says "Super Admin role cannot be assigned by normal admins", 
        // "Super Admin cannot delete themselves".
        // It doesn't explicitly say they can't delete OTHER super admins, but it's safer to ask confirmation or allow it since they have full control.
        // We will allow it but audit it heavily.

        require_once BASE_PATH . '/app/Models/User.php';
        
        try {
            User::delete($id);
            ActivityLogger::log('delete', 'user', $id, ['by' => Auth::user()['username']]);
            Auth::flash('success', 'User deleted.');
        } catch (Exception $e) {
            Auth::flash('error', 'Delete failed.');
        }
        $this->redirect('/users');
    }
}
