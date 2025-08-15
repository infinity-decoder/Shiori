<?php
class User
{
    public static function getByUsername(string $username): ?array
    {
        $pdo  = DB::get();
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function updateLastLogin(int $id): void
    {
        $pdo  = DB::get();
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }
}
