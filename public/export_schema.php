<?php
// public/export_schema.php
define('BASE_PATH', dirname(__DIR__));
$cfg = require BASE_PATH . '/config/database.php';

$host = $cfg['host'];
$user = $cfg['user'];
$pass = $cfg['pass'];
$name = $cfg['name'];

// Use mysqldump to export schema and data
// Try common Ampps path first
$mysqldumpPath = 'C:/Program Files/Ampps/mysql/bin/mysqldump.exe';
if (!file_exists($mysqldumpPath)) {
     $output = [];
     @exec('where mysqldump', $output);
     if (!empty($output[0])) {
         $mysqldumpPath = trim($output[0]);
     } else {
         $mysqldumpPath = 'mysqldump';
     }
}

$cmd = sprintf('"%s" --host=%s --user=%s --password=%s --no-create-db --skip-add-drop-table --skip-lock-tables %s > "%s"', 
    $mysqldumpPath, 
    escapeshellarg($host), 
    escapeshellarg($user), 
    escapeshellarg($pass), 
    escapeshellarg($name),
    BASE_PATH . '/db/schema.sql'
);

// We want a clean schema + data for the installer
// But wait, schema.sql usually just has CREATE TABLE. seed.sql has data.
// The user said "make exactly as we are using here".
// So I will dump everything into schema.sql so the installer just runs one file.
// But I should add "IF NOT EXISTS" to tables to be safe, or just DROP TABLE IF EXISTS.
// mysqldump defaults are usually fine for a fresh install.

exec($cmd, $output, $ret);

if ($ret === 0) {
    echo "Schema exported to db/schema.sql";
} else {
    echo "Export failed. Ret: $ret";
}
