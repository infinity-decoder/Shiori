<?php
// Auto-detect base URL for portability
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// script_name is usually /Shiori/public/index.php. We want /Shiori/public
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . $host . $scriptDir;

return [
    'name' => 'Shiori',
    'base_url' => $baseUrl,
    'env'   => 'local',
    'debug' => true,
];
