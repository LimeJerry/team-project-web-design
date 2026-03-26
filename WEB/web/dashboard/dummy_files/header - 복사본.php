<?php
session_start();

if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: ../index.php");
  exit;
}

if (!isset($_SESSION['admin'])) {
  header("Location: ../index.php");
  exit;
}

$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>로봇 관리자 시스템</title>

<style>
@font-face {
  font-family: 'NanumSquare';
  src: url('../NanumSquareL.otf') format('opentype');
  font-weight: 300;
}
@font-face {
  font-family: 'NanumSquare';
  src: url('../NanumSquareEB.otf') format('opentype');
  font-weight: 800;
}

* { box-sizing: border-box; font-family: 'NanumSquare', sans-serif; }

html, body { margin:0; padding:0; }
body {
  background:#F4FAFD;
  color:#1F2933;
  overflow-x: hidden; /* ✅ 가로 스크롤 방지 */
}

.header h1 { font-weight: 800; margin:0; }
.header{
  background:#6EC1E4;
  padding:18px 32px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:16px;
}

.logout{
  text-decoration:none;
  color:#fff;
  background:#4A9CC7;
  padding:8px 16px;
  border-radius:10px;
  white-space: nowrap;
}

.nav{
  background:#F7FCFF;
  padding:12px 32px;
  display:flex;
  gap:20px;
  border-bottom:2px solid #BFE3F2;
  flex-wrap: wrap; /* ✅ 좁아지면 줄바꿈 */
}

.nav a{
  text-decoration:none;
  font-weight:600;
  padding:8px 14px;
  border-radius:8px;
  color:#4A9CC7;
  display:inline-block;
}

.nav a.active{
  background:#6EC1E4;
  color:white;
}

.container{
  padding:30px;
  max-width: 1280px;   /* ✅ 너무 넓게 퍼지지 않게 */
  margin: 0 auto;      /* ✅ 가운데 정렬 */
  width: 100%;
}

.box{
  background:white;
  padding:20px;
  border-radius:16px;
  border:2px solid #BFE3F2;
}

.section-title{
  font-weight:600;
  margin-bottom:12px;
  color:#4A9CC7;
  font-size: 18px;
}
</style>
</head>
<body>

<div class="header">
  <h1>로봇 관리자 시스템</h1>
  <a href="?logout=1" class="logout">로그아웃</a>
</div>

<div class="nav">
  <a href="cam_dashboard.php" class="<?= $current=='cam_dashboard.php'?'active':'' ?>">카메라 모니터링</a>
  <a href="inform_dashboard.php" class="<?= $current=='inform_dashboard.php'?'active':'' ?>">감지 정보 및 로그</a>
</div>