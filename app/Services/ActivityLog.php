<?php
class ActivityLog
{
    public static function log(?int $userId, string $action, string $entity, ?int $entityId = null, ?string $details = null): void
    {
        try {
            $pdo = DB::get();
            $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, entity, entity_id, details, ip) VALUES (?, ?, ?, ?, ?, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $stmt->execute([
                $userId,
                $action,
                $entity,
                $entityId,
                $details,
                $ip,
            ]);
        } catch (Throwable $e) {
            // Do not break the app on logging failure. Optionally write to file.
            $logDir = BASE_PATH . '/storage/logs';
            if (is_dir($logDir)) {
                @file_put_contents($logDir . '/activity_log_errors.log', '['.date('c').'] '.$e->getMessage().PHP_EOL, FILE_APPEND);
            }
        }
    }
}
