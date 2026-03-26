<?php
require_once __DIR__ . '/image_loader.php';

$files = getDetectionImageFiles();
$signature = buildDetectionImageSignature($files);

if (!$files) {
    echo json_encode([
        "latest" => null,
        "signature" => $signature,
        "count" => 0
    ]);
    exit;
}

$groups = parseDetectionGroups($files);

/*
 🔥 핵심: YOLO + SLAM 둘 다 있는 timestamp만 인정
*/
$completeGroups = [];

foreach ($groups as $groupKey => $images) {
    if (isset($images['YOLO']) && isset($images['SLAM'])) {
        $completeGroups[$groupKey] = true;
    }
}

if (empty($completeGroups)) {
    echo json_encode([
        "latest" => null,
        "signature" => $signature,
        "count" => 0
    ]);
    exit;
}

$latestTimestamp = array_key_first($completeGroups);

echo json_encode([
    "latest" => $latestTimestamp,
    "signature" => $signature,
    "count" => count($completeGroups)
]);
