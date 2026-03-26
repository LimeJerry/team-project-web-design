<?php
require_once __DIR__ . '/image_loader.php';

$filename = isset($_GET['file']) ? basename((string)$_GET['file']) : '';
$imagePath = $filename !== '' ? resolveDetectionImagePath($filename) : null;

if ($imagePath === null || !is_file($imagePath)) {
    http_response_code(404);
    exit('Image not found');
}

$extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
];

header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
header('Content-Length: ' . (string)filesize($imagePath));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

readfile($imagePath);
