<?php
/**
 * Session Model
 * 
 * Manages academic year sessions (e.g., 2024-2025)
 * Allows dynamic addition of new sessions through admin panel
 */

class Session
{
    /**
     * Get all sessions
     * @param bool $onlyActive Get only active sessions
     * @return array Array of session records
     */
    public static function getAll(bool $onlyActive = true): array
    {
        $pdo = DB::get();
        $sql = "SELECT * FROM sessions";
        
        if ($onlyActive) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY session_year DESC";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find a specific session by ID
     * @param int $id Session ID
     * @return array|null Session record or null
     */
    public static function find(int $id): ?array
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    
    /**
     * Create a new session
     * @param string $sessionYear Session year (e.g., "2024-2025")
     * @return int New session ID
     * @throws PDOException if session already exists
     */
    public static function create(string $sessionYear): int
    {
        $pdo = DB::get();
        
        // Validate format (YYYY-YYYY)
        if (!preg_match('/^\d{4}-\d{4}$/', $sessionYear)) {
            throw new InvalidArgumentException('Invalid session year format. Use YYYY-YYYY (e.g., 2024-2025)');
        }
        
        // Sanitize input
        $sessionYear = trim($sessionYear);
        
        $stmt = $pdo->prepare("
            INSERT INTO sessions (session_year, is_active)
            VALUES (?, 1)
        ");
        
        $stmt->execute([$sessionYear]);
        return (int)$pdo->lastInsertId();
    }
    
    /**
     * Toggle session active status
     * @param int $id Session ID
     * @return void
     */
    public static function toggle(int $id): void
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("
            UPDATE sessions 
            SET is_active = NOT is_active 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
    }
    
    /**
     * Delete a session
     * @param int $id Session ID
     * @return bool True if deleted, false if session is in use
     * @throws PDOException on database error
     */
    public static function delete(int $id): bool
    {
        $pdo = DB::get();
        
        // First check if any students use this session
        $session = self::find($id);
        if (!$session) {
            return false;
        }
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM students 
            WHERE session = ?
        ");
        $stmt->execute([$session['session_year']]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($count > 0) {
            // Session is in use, cannot delete
            return false;
        }
        
        // Safe to delete
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        
        return true;
    }
    
    /**
     * Check if a session year already exists
     * @param string $sessionYear Session year to check
     * @return bool True if exists
     */
    public static function exists(string $sessionYear): bool
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM sessions WHERE session_year = ?");
        $stmt->execute([$sessionYear]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }
    
    /**
     * Seed default sessions (2020-2030)
     * Only runs if sessions table is empty
     * @return void
     */
    public static function seedDefaults(): void
    {
        $pdo = DB::get();
        
        // Check if sessions exist
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sessions");
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return; // Already seeded
        }
        
        // Seed sessions from 2020 to 2030
        $stmt = $pdo->prepare("INSERT INTO sessions (session_year, is_active) VALUES (?, 1)");
        
        for ($year = 2020; $year <= 2030; $year++) {
            $sessionYear = sprintf('%d-%d', $year, $year + 1);
            $stmt->execute([$sessionYear]);
        }
    }
}
