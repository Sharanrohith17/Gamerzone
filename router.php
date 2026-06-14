<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$normalized = str_replace('\\', '/', $path);

if (str_starts_with($normalized, '/data/')) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

$file = __DIR__ . $normalized;
if ($normalized !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
