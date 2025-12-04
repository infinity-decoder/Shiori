<?php
// Root installer helper
// Redirects to public/ which handles the installation logic if config is missing

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Redirect to public/
header("Location: $protocol$host$uri/public/");
exit;
