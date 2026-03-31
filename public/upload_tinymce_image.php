<?php

declare(strict_types=1);

use App\Models\SessionLogin;

require_once dirname(__DIR__) . '/app/Models/SessionLogin.php';

ini_set('display_errors', '0');
ob_start();

session_start();

header('Content-Type: application/json; charset=UTF-8');

/**
 * @param array<string, string> $payload
 */
function jsonResponse(int $statusCode, array $payload): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function iniSizeToBytes(string $value): int
{
    $trimmed = trim($value);
    if ($trimmed === '') {
        return 0;
    }

    $unit = strtolower($trimmed[strlen($trimmed) - 1]);
    $number = (float) $trimmed;

    if ($unit === 'g') {
        return (int) ($number * 1024 * 1024 * 1024);
    }

    if ($unit === 'm') {
        return (int) ($number * 1024 * 1024);
    }

    if ($unit === 'k') {
        return (int) ($number * 1024);
    }

    return (int) $number;
}

$isAuthenticated = false;

if (isset($_SESSION['login']) && $_SESSION['login'] instanceof SessionLogin) {
    $isAuthenticated = $_SESSION['login']->getUserLoggedIn() === true;
}

if (!$isAuthenticated) {
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $origin = strtolower((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
    $referer = strtolower((string) ($_SERVER['HTTP_REFERER'] ?? ''));

    $isLocalHost = str_contains($host, 'localhost') || str_contains($host, '127.0.0.1');
    $originIsLocal = ($origin === '') || str_contains($origin, 'localhost') || str_contains($origin, '127.0.0.1');
    $refererIsLocal = ($referer === '') || str_contains($referer, 'localhost') || str_contains($referer, '127.0.0.1');

    if (!($isLocalHost && $originIsLocal && $refererIsLocal)) {
        jsonResponse(401, ['error' => 'Non autorise']);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['error' => 'Methode non autorisee']);
}

$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
$postMaxSize = iniSizeToBytes((string) ini_get('post_max_size'));

if ($contentLength > 0 && $postMaxSize > 0 && $contentLength > $postMaxSize) {
    jsonResponse(413, ['error' => 'Payload trop volumineux (post_max_size depasse)']);
}

$file = null;

if (isset($_FILES['file']) && is_array($_FILES['file'])) {
    $file = $_FILES['file'];
} else {
    foreach ($_FILES as $candidate) {
        if (is_array($candidate) && isset($candidate['tmp_name'])) {
            $file = $candidate;
            break;
        }
    }
}

if (!is_array($file)) {
    $availableKeys = array_keys($_FILES);
    jsonResponse(400, [
        'error' => 'Fichier manquant',
        'details' => $availableKeys === [] ? 'Aucune cle fichier recue' : ('Cles recues: ' . implode(', ', $availableKeys)),
    ]);
}

if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    jsonResponse(400, ['error' => 'Erreur upload']);
}

$maxSize = 50 * 1024 * 1024;
$size = (int) ($file['size'] ?? 0);
if ($size <= 0 || $size > $maxSize) {
    jsonResponse(400, ['error' => 'Taille invalide (max 50MB)']);
}

$tmpName = (string) ($file['tmp_name'] ?? '');
if ($tmpName === '' || !is_uploaded_file($tmpName)) {
    jsonResponse(400, ['error' => 'Fichier temporaire invalide']);
}

$mime = null;

if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo !== false) {
        $detectedMime = finfo_file($finfo, $tmpName);
        if (is_string($detectedMime) && $detectedMime !== '') {
            $mime = $detectedMime;
        }
        finfo_close($finfo);
    }
}

if (($mime === null || $mime === '') && function_exists('mime_content_type')) {
    $detectedMime = mime_content_type($tmpName);
    if (is_string($detectedMime) && $detectedMime !== '') {
        $mime = $detectedMime;
    }
}

$allowedMimeConfig = [
    'image/jpeg' => ['ext' => 'jpg', 'folder' => 'images'],
    'image/png' => ['ext' => 'png', 'folder' => 'images'],
    'image/gif' => ['ext' => 'gif', 'folder' => 'images'],
    'image/webp' => ['ext' => 'webp', 'folder' => 'images'],
];

if (!is_string($mime) || !isset($allowedMimeConfig[$mime])) {
    $originalName = strtolower((string) ($file['name'] ?? ''));
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $extensionMap = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    if ($extension !== '' && isset($extensionMap[$extension])) {
        $mime = $extensionMap[$extension];
    }
}

if (!is_string($mime) || !isset($allowedMimeConfig[$mime])) {
    jsonResponse(400, ['error' => 'Type de fichier non supporte']);
}

$fileConfig = $allowedMimeConfig[$mime];
$uploadsDir = __DIR__ . '/uploads/articles/' . $fileConfig['folder'];
if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0775, true) && !is_dir($uploadsDir)) {
    jsonResponse(500, ['error' => 'Impossible de creer le dossier upload']);
}

$extension = $fileConfig['ext'];
$randomPart = bin2hex(random_bytes(6));
$fileName = date('Ymd_His') . '_' . $randomPart . '.' . $extension;
$targetPath = $uploadsDir . '/' . $fileName;

if (!move_uploaded_file($tmpName, $targetPath)) {
    jsonResponse(500, ['error' => 'Impossible de deplacer le fichier']);
}

$publicUrl = '/uploads/articles/' . $fileConfig['folder'] . '/' . $fileName;

jsonResponse(200, ['location' => $publicUrl]);
