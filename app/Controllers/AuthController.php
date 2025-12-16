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
}
