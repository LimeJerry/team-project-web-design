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

* {
  box-sizing: border-box;
  font-family: 'NanumSquare', sans-serif;
}

body {
  margin:0;
  background:#F4FAFD;
  color:#1F2933;
}

.header h1 {
  font-weight: 800;
}

.header{
  background:#6EC1E4;
  padding:18px 32px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.logout{
  text-decoration:none;
  color:#fff;
  background:#4A9CC7;
  padding:8px 16px;
  border-radius:10px;
}

.nav{
  background:#F7FCFF;
  padding:12px 32px;
  display:flex;
  gap:20px;
  border-bottom:2px solid #BFE3F2;
}

.nav a{
  text-decoration:none;
  font-weight:600;
  padding:8px 14px;
  border-radius:8px;
  color:#4A9CC7;
}

.nav a.active{
  background:#6EC1E4;
  color:white;
}

.container{
  padding:30px;
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
}
</style>
</head>
<body>

<div class="header">
  <h1>로봇 관리자 시스템</h1>
  <a href="?logout=1" class="logout">로그아웃</a>
</div>

<div class="nav">
  <a href="cam_dashboard.php" class="<?= $current=='cam_dashboard.php'?'active':'' ?>">
    카메라 모니터링
  </a>

  <a href="inform_dashboard.php" class="<?= $current=='inform_dashboard.php'?'active':'' ?>">
    감지 정보 및 로그
  </a>
</div>