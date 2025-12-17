<?php

class DatabaseBackupService
{
    /**
     * Create a database backup
     * 
     * @param array $config Database configuration
     * @return string Path to backup file
     * @throws Exception
     */
    public static function createBackup(array $config): string
    {
        $backupDir = BASE_PATH . '/storage/backups';
        if (!is_dir($backupDir)) {
            if (!mkdir($backupDir, 0755, true)) {
                throw new Exception("Could not create backup directory: $backupDir");
            }
        }

        // Sanitize database name for filename
        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $config['name']);
        if (empty($dbName)) $dbName = 'backup';
        
        $filename = $dbName . '_backup_' . date('Ymd_His') . '.sql';
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Try Native PHP Backup (Most reliable for portability)
        try {
            self::backupNative($config, $filePath);
            return $filePath;
        } catch (Exception $e) {
            // Log error or rethrow
            throw new Exception("Backup failed: " . $e->getMessage());
        }
    }

    /**
     * Perform backup using PHP PDO (No exec() required)
     */
    private static function backupNative(array $config, string $filePath): void
    {
        $pdo = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $handle = fopen($filePath, 'w');
        if (!$handle) throw new Exception("Cannot write to file: $filePath");

        // Write Header
        fwrite($handle, "-- Shiori Database Backup\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Host: {$config['host']}\n");
        fwrite($handle, "-- Database: {$config['name']}\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n");

        // Get Tables
        $tables = [];
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            // Structure
            fwrite($handle, "-- Table structure for table `$table`\n");
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            
            $row = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            fwrite($handle, $row[1] . ";\n\n");

            // Data
            fwrite($handle, "-- Dumping data for table `$table`\n");
            
            // Buffered query for large tables
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rowCount = 0;
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($rowCount % 100 == 0) {
                    if ($rowCount > 0) fwrite($handle, ";\n");
                    fwrite($handle, "INSERT INTO `$table` VALUES ");
                } else {
                    fwrite($handle, ",\n");
                }
                
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = "NULL";
                    } else {
                        $values[] = $pdo->quote($value);
                    }
                }
                fwrite($handle, "(" . implode(", ", $values) . ")");
                $rowCount++;
            }
            
            if ($rowCount > 0) {
                fwrite($handle, ";\n");
            }
            fwrite($handle, "\n\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }
}
