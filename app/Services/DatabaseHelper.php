<?php

class DatabaseHelper
{
    /**
     * Safely checks and creates the settings table if missing.
     * Use this in Controllers where the table is accessed.
     */
    public static function ensureSettingsTable(): void
    {
        $pdo = DB::get();
        
        // Defensive check: If table exists, do nothing (fastest)
        try {
            $result = $pdo->query("SHOW TABLES LIKE 'settings'");
            if ($result->rowCount() > 0) {
                return;
            }
        } catch (PDOException $e) {
            // If checking failed, we might have bigger issues, but try to proceed to create
        }

        // Create table
        $sql = "CREATE TABLE IF NOT EXISTS `settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `setting_key` varchar(50) NOT NULL,
          `setting_value` text,
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Failed to create settings table: " . $e->getMessage());
        }
    }

    /**
     * Ensure Activity Logs table exists
     */
    public static function ensureActivityLogsTable(): void
    {
        $pdo = DB::get();
        // Defensive check
        $result = $pdo->query("SHOW TABLES LIKE 'activity_logs'");
        if ($result->rowCount() > 0) return;

        $sql = "CREATE TABLE IF NOT EXISTS `activity_logs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) DEFAULT NULL,
          `action` varchar(50) NOT NULL,
          `entity_type` varchar(50) NOT NULL,
          `entity_id` int(11) DEFAULT NULL,
          `details` text,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $pdo->exec($sql);
    }
}
