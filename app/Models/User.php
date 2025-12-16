<?php
class User
{
    public static function getByUsername(string $username): ?array
    {
        $pdo  = DB::get();
        // Select * to get is_active and other new fields
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function validatePasswordStrength(string $password): ?string
    {
        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            return "Password must contain at least one number.";
        }
        return null; // Valid
    }

    public static function get(int $id): ?array
    {
        $pdo = DB::get();
        // Use * or select columns explicitly
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getAll(bool $activeOnly = false): array
    {
        $pdo = DB::get();
        $sql = "SELECT * FROM users";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY id DESC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function create(array $data): bool
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([
            $data['name'],
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['role'] ?? 'viewer',
            $data['is_active'] ?? 1
        ]);
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = DB::get();
        // Dynamic update construction
        $set = [];
        $params = [];
        foreach ($data as $col => $val) {
            $set[] = "$col = ?";
            $params[] = $val;
        }
        $params[] = $id; 
        
        if (empty($set)) return false;

        $sql = "UPDATE users SET " . implode(', ', $set) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function exists(string $username, string $email, int $excludeId = 0): bool
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1");
        $stmt->execute([$username, $email, $excludeId]);
        return (bool)$stmt->fetch();
    }

    public static function updateLastLogin(int $id): void
    {
        $pdo  = DB::get();
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }
}
