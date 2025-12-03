<?php

class ActivityLogger
{
    public static function log(string $action, string $entityType, int $entityId, ?array $details = null): void
    {
        if (!Auth::check()) {
            return;
        }

        $userId = Auth::user()['id'];
        $detailsJson = $details ? json_encode($details) : null;

        $pdo = DB::get();
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $action, $entityType, $entityId, $detailsJson]);
    }
}
