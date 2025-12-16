<?php
class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login.php', ['title' => 'Login | Shiori']);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }

        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid session. Please try again.');
            $this->redirect('/login');
        }

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            Auth::flash('error', 'Username and password are required.');
            $this->redirect('/login');
        }

        $user = User::getByUsername($username);
        if (!$user || !User::verifyPassword($password, $user['password_hash'])) {
            Auth::flash('error', 'Invalid credentials.');
            $this->redirect('/login');
        }

        if (isset($user['is_active']) && (int)$user['is_active'] !== 1) {
            Auth::flash('error', 'Your account is deactivated. Please contact administrator.');
            $this->redirect('/login');
        }

        // Success: login + last_login update
        Auth::login($user);
        User::updateLastLogin((int)$user['id']);

        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF check is nice-to-have on logout as well
            if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
                Auth::flash('error', 'Invalid CSRF token on logout.');
            }
            Auth::logout();
        }
        $this->redirect('/login');
    }

    public function changePasswordForm(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
        $this->view('auth/change_password.php', ['title' => 'Change Password | Shiori']);
    }

    public function changePassword(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile/change-password');
        }

        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/profile/change-password');
        }

        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword     = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        // 1. Verify current password
        $user = Auth::user();
        // Since session might not store the hash, we need to fetch fresh user data
        $dbUser = User::get((int)$user['id']);
        
        if (!$dbUser || !password_verify($currentPassword, $dbUser['password_hash'])) {
            Auth::flash('error', 'Current password is incorrect.');
            $this->redirect('/profile/change-password');
        }

        // 2. Validate new password strength
        $strengthError = User::validatePasswordStrength($newPassword);
        if ($strengthError) {
            Auth::flash('error', $strengthError);
            $this->redirect('/profile/change-password');
        }

        // 3. Confirm match
        if ($newPassword !== $confirmPassword) {
            Auth::flash('error', 'New passwords do not match.');
            $this->redirect('/profile/change-password');
        }

        // 4. Update
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        try {
            User::update((int)$user['id'], ['password_hash' => $hash]);
            
            // Optional: Log activity
            require_once BASE_PATH . '/app/Services/ActivityLog.php';
            ActivityLogger::log('update', 'user', (int)$user['id'], ['action' => 'password_change']);

            Auth::flash('success', 'Password changed successfully.');
            $this->redirect('/dashboard');
        } catch (Exception $e) {
            Auth::flash('error', 'Failed to update password.');
            $this->redirect('/profile/change-password');
        }
    }
}
