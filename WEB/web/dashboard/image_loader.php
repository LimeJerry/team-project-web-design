<?php

function getConfiguredImageDirectory(): string
{
    static $resolvedDir = null;

    if ($resolvedDir !== null) {
        return $resolvedDir;
    }

    $defaultDir = __DIR__ . DIRECTORY_SEPARATOR . 'images';
    $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

    if (is_file($envPath)) {
        $env = parse_ini_file($envPath);

        if ($env !== false && !empty($env['IMAGE_DIR'])) {
            $candidate = trim($env['IMAGE_DIR']);

            if (!preg_match('/^[A-Za-z]:\\\\|^\//', $candidate)) {
                $candidate = dirname(__DIR__) . DIRECTORY_SEPARATOR . $candidate;
            }

            if (is_dir($candidate)) {
                $resolvedDir = realpath($candidate) ?: $candidate;
                return $resolvedDir;
            }
        }
    }

    $resolvedDir = realpath($defaultDir) ?: $defaultDir;
    return $resolvedDir;
}

function getDetectionImageFiles(): array
{
    $imageDir = getConfiguredImageDirectory();
    $files = glob($imageDir . DIRECTORY_SEPARATOR . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
    $files = $files ?: [];
    rsort($files);

    return $files;
}

function buildDetectionImageSignature(array $files): string
{
    $signatureParts = [];

    foreach ($files as $file) {
        $signatureParts[] = basename($file) . ':' . @filemtime($file);
    }

    return md5(implode('|', $signatureParts));
}

function parseDetectionGroups(array $files): array
{
    $groups = [];

    foreach ($files as $file) {
        $filename = basename($file);

        if (!preg_match('/([A-Z0-9]+F)_(\d{8}_\d{6})_(YOLO|SLAM)\.(jpg|jpeg|png)$/i', $filename, $matches)) {
            continue;
        }

        $floor = strtoupper($matches[1]);
        $timestamp = $matches[2];
        $type = strtoupper($matches[3]);
        $groupKey = $floor . '_' . $timestamp;

        if (!isset($groups[$groupKey])) {
            $groups[$groupKey] = [
                'floor' => $floor,
                'timestamp' => $timestamp,
            ];
        }

        $groups[$groupKey][$type] = $filename;
    }

    uasort($groups, function (array $left, array $right): int {
        $timestampCompare = strcmp($right['timestamp'], $left['timestamp']);

        if ($timestampCompare !== 0) {
            return $timestampCompare;
        }

        return strcmp($right['floor'], $left['floor']);
    });

    return $groups;
}

function buildDetectionImageUrl(string $filename): string
{
    return 'image_view.php?file=' . rawurlencode($filename);
}

function resolveDetectionImagePath(string $filename): ?string
{
    $baseDir = realpath(getConfiguredImageDirectory());

    if ($baseDir === false) {
        return null;
    }

    $candidate = realpath($baseDir . DIRECTORY_SEPARATOR . $filename);

    if ($candidate === false) {
        return null;
    }

    if (strpos($candidate, $baseDir . DIRECTORY_SEPARATOR) !== 0 && $candidate !== $baseDir) {
        return null;
    }

    return $candidate;
}
