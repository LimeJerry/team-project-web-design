<?php

$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    die(".env 파일을 찾을 수 없습니다.");
}

$env = parse_ini_file($envPath);

if ($env === false) {
    die(".env 파일을 읽을 수 없습니다.");
}

$ADMIN_ID = $env['ADMIN_ID'] ?? null;
$ADMIN_PW = $env['ADMIN_PW'] ?? null;

if (!$ADMIN_ID || !$ADMIN_PW) {
    die(".env 설정이 잘못되었습니다.");
}