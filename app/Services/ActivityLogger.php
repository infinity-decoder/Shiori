<?php

class ActivityLogger
{
    public static function log(string $action, string $entityType, int $entityId, array $details = []): void
    {
        $pdo = DB::get();
        $user = Auth::user();
        $userId = $user['id'] ?? 0;

        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            json_encode($details)
        ]);
    }

    public static function getLatest(int $limit = 50): array
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("
            SELECT l.*, u.username 
            FROM activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
