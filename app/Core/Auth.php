<?php
class Auth
{
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => (int)$user['id'],
            'username' => $user['username'],
            'role'     => $user['role'] ?? 'admin',
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string
    {
        if (!empty($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }

    public static function hasRole(string $role): bool
    {
        $user = self::user();
        return $user && ($user['role'] === $role);
    }

    public static function isSuperAdmin(): bool
    {
        return self::hasRole('super_admin');
    }

    public static function isAdmin(): bool
    {
        // Admin includes Super Admin (Hierarchy)
        return self::hasRole('admin') || self::hasRole('super_admin');
    }

    public static function isStaff(): bool
    {
        return self::hasRole('staff');
    }

    public static function isViewer(): bool
    {
        return self::hasRole('viewer');
    }

    /**
     * Store old input data for form repopulation after validation errors
     */
    public static function setOldInput(array $data): void
    {
        $_SESSION['old_input'] = $data;
    }

    /**
     * Retrieve old input value by key
     */
    public static function getOldInput(string $key = null, $default = '')
    {
        if ($key === null) {
            $oldInput = $_SESSION['old_input'] ?? [];
            unset($_SESSION['old_input']);
            return $oldInput;
        }
        return $_SESSION['old_input'][$key] ?? $default;
    }

    /**
     * Check if old input exists
     */
    public static function hasOldInput(string $key = null): bool
    {
        if ($key === null) {
            return !empty($_SESSION['old_input']);
        }
        return isset($_SESSION['old_input'][$key]);
    }

    /**
     * Clear old input data
     */
    public static function flushOldInput(): void
    {
        unset($_SESSION['old_input']);
    }
}
