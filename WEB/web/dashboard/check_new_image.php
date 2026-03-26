<?php

$imageDir = __DIR__ . DIRECTORY_SEPARATOR . 'images';
$files = glob($imageDir . DIRECTORY_SEPARATOR . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
$files = $files ?: [];

$signatureParts = [];
foreach ($files as $file) {
    $signatureParts[] = basename($file) . ':' . @filemtime($file);
}
$signature = md5(implode('|', $signatureParts));

if (!$files) {
    echo json_encode([
        "latest" => null,
        "signature" => $signature,
        "count" => 0
    ]);
    exit;
}

$groups = [];

foreach ($files as $file) {
    $filename = basename($file);

    if (preg_match('/([A-Z0-9]+F)_(\d{8}_\d{6})_(YOLO|SLAM)\.(jpg|jpeg|png)$/i', $filename, $m)) {
        $floor = strtoupper($m[1]);
        $timestamp = $m[2];
        $type = strtoupper($m[3]);
        $groupKey = $floor . '_' . $timestamp;

        if (!isset($groups[$groupKey])) {
            $groups[$groupKey] = [];
        }

        $groups[$groupKey][$type] = true;
    }
}

/*
 🔥 핵심: YOLO + SLAM 둘 다 있는 timestamp만 인정
*/
$completeGroups = [];

foreach ($groups as $timestamp => $types) {
    if (isset($types['YOLO']) && isset($types['SLAM'])) {
        $completeGroups[$timestamp] = true;
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

krsort($completeGroups);

$latestTimestamp = array_key_first($completeGroups);

echo json_encode([
    "latest" => $latestTimestamp,
    "signature" => $signature,
    "count" => count($completeGroups)
]);
