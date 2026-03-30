<?php

declare(strict_types=1);

$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$documentRoot = __DIR__;
$filePath = realpath($documentRoot . $requestedPath);

if (is_string($filePath) && str_starts_with($filePath, realpath($documentRoot)) && is_file($filePath)) {
    return false;
}

require __DIR__ . '/index.php';
