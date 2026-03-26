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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>순찰 로봇 관리자 대시보드</title>

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

:root {
  --bg: #edf3f7;
  --panel: #f7fbfd;
  --surface: #ffffff;
  --surface-strong: #0f5d78;
  --surface-strong-2: #0a4b60;
  --border: #8eb9c8;
  --border-strong: #08384a;
  --text: #163140;
  --muted: #507081;
  --accent: #1f7c9b;
  --accent-soft: #dcedf4;
  --shadow: 0 20px 40px rgba(8, 56, 74, 0.08);
}

* {
  box-sizing: border-box;
  font-family: 'NanumSquare', sans-serif;
}

html,
body {
  margin: 0;
  padding: 0;
}

body {
  min-height: 100vh;
  background:
    radial-gradient(circle at top left, rgba(31, 124, 155, 0.12), transparent 28%),
    linear-gradient(180deg, #f3f8fb 0%, #e8eff4 100%);
  color: var(--text);
}

a {
  color: inherit;
}

.app-shell {
  width: min(1500px, calc(100% - 32px));
  margin: 20px auto;
  background: rgba(255, 255, 255, 0.72);
  border: 1px solid rgba(142, 185, 200, 0.5);
  border-radius: 26px;
  box-shadow: var(--shadow);
  overflow: hidden;
  backdrop-filter: blur(10px);
}

.header {
  background: linear-gradient(135deg, var(--surface-strong-2), var(--surface-strong));
  color: #f5fbff;
  padding: 22px 28px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.brand {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.brand-title {
  margin: 0;
  font-size: clamp(22px, 2vw, 30px);
  font-weight: 800;
  letter-spacing: -0.02em;
}

.logout {
  text-decoration: none;
  color: var(--surface-strong-2);
  background: #ffffff;
  padding: 12px 18px;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.7);
  font-weight: 800;
  white-space: nowrap;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.logout:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
}

.nav {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  padding: 14px 28px 18px;
  background: linear-gradient(180deg, rgba(220, 237, 244, 0.7), rgba(255, 255, 255, 0.88));
  border-bottom: 1px solid rgba(142, 185, 200, 0.45);
}

.nav a {
  text-decoration: none;
  font-weight: 800;
  padding: 10px 16px;
  border-radius: 999px;
  color: var(--accent);
  background: rgba(255, 255, 255, 0.72);
  border: 1px solid transparent;
}

.nav a.active {
  background: var(--surface-strong);
  color: #ffffff;
  border-color: rgba(8, 56, 74, 0.35);
}

.page-body {
  padding: 24px 24px 30px;
}

.section-card {
  background: var(--surface);
  border: 1px solid rgba(142, 185, 200, 0.6);
  border-radius: 22px;
  box-shadow: 0 18px 34px rgba(8, 56, 74, 0.06);
}

.section-title {
  margin: 0;
  color: var(--surface-strong);
  font-size: 20px;
  font-weight: 800;
  letter-spacing: -0.02em;
}

.section-subtitle {
  margin: 6px 0 0;
  color: var(--muted);
  font-size: 14px;
}

@media (max-width: 900px) {
  .app-shell {
    width: min(100%, calc(100% - 18px));
    margin: 10px auto;
    border-radius: 20px;
  }

  .header {
    padding: 18px;
    flex-direction: column;
    align-items: stretch;
  }

  .logout {
    text-align: center;
  }

  .nav {
    padding: 12px 18px 16px;
  }

  .page-body {
    padding: 18px 14px 22px;
  }
}
</style>
</head>
<body>

<div class="app-shell">
  <div class="header">
    <div class="brand">
      <h1 class="brand-title">순찰 로봇 관리자 대시보드</h1>
    </div>
    <a href="?logout=1" class="logout">로그아웃</a>
  </div>

  <div class="nav">
    <a href="cam_dashboard.php" class="<?= $current === 'cam_dashboard.php' ? 'active' : '' ?>">실시간 모니터링</a>
    <a href="inform_dashboard.php" class="<?= $current === 'inform_dashboard.php' ? 'active' : '' ?>">감지 정보 및 로그</a>
  </div>

  <div class="page-body">
