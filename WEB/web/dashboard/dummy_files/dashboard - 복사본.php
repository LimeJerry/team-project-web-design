<?php
session_start();

/* 로그아웃 처리 */
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: ../index.php");
  exit;
}

/* 로그인 체크 */
if (!isset($_SESSION['admin'])) {
  header("Location: ../index.php");
  exit;
}

/* 임시 로그 데이터 */
$robotLogs = [
  ["time" => "14:20", "dest" => "302호", "eta" => "14:32", "status" => "이동 중"],
  ["time" => "14:10", "dest" => "305호", "eta" => "14:18", "status" => "도착 완료"],
  ["time" => "14:00", "dest" => "401호", "eta" => "14:12", "status" => "도착 완료"],
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>로봇 관리자 대시보드</title>

  <style>
    * {
      box-sizing: border-box;
      font-family: "Segoe UI", "Apple SD Gothic Neo", sans-serif;
    }

    body {
      margin: 0;
      background: #F4FAFD;
      color: #1F2933;
    }

    /* ===== Header ===== */
    .header {
      background: #6EC1E4;
      padding: 18px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #BFE3F2;
    }

    .header h1 {
      margin: 0;
      font-size: 20px;
      font-weight: 600;
    }

    .logout {
      text-decoration: none;
      color: white;
      background: #4A9CC7;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 14px;
    }

    .logout:hover {
      background: #3B8BB3;
    }

    /* ===== Layout ===== */
    .container {
      display: flex;
      gap: 24px;
      padding: 28px;
    }

    /* ===== Camera ===== */
    .camera-box {
      flex: 1.2;
      background: white;
      border-radius: 16px;
      padding: 20px;
      border: 2px solid #BFE3F2;
    }

    .section-title {
      font-size: 16px;
      font-weight: 600;
      color: #4A9CC7;
      margin-bottom: 12px;
    }

    .camera-stream {
      height: 420px;
      border-radius: 12px;
      background: #000;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6EC1E4;
      font-size: 15px;
    }

    /* ===== Log ===== */
    .log-box {
      flex: 1;
      background: white;
      border-radius: 16px;
      padding: 20px;
      border: 2px solid #BFE3F2;
      max-height: 520px;
      overflow-y: auto;
    }

    .log-item {
      padding: 14px 16px;
      margin-bottom: 14px;
      border-left: 4px solid #6EC1E4;
      background: #F7FCFF;
      border-radius: 8px;
    }

    .log-time {
      font-size: 12px;
      color: #6B7280;
      margin-bottom: 6px;
    }

    .log-dest {
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .log-eta {
      font-size: 14px;
      color: #374151;
    }

    .log-status {
      margin-top: 6px;
      font-size: 13px;
      color: #4A9CC7;
      font-weight: 600;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="header">
    <h1>로봇 관리자 대시보드</h1>
    <a href="dashboard.php?logout=1" class="logout">로그아웃</a>
  </div>

  <!-- Main -->
  <div class="container">

    <!-- Camera -->
    <div class="camera-box">
      <div class="section-title">라이브 카메라</div>
      <div class="camera-stream">
        Camera Stream
      </div>
    </div>

    <!-- Log -->
    <div class="log-box">
      <div class="section-title">로그</div>

      <?php foreach ($robotLogs as $log): ?>
        <div class="log-item">
          <div class="log-time"><?= $log['time'] ?></div>
          <div class="log-dest">목적지: <?= $log['dest'] ?></div>
          <div class="log-eta">ETA: <?= $log['eta'] ?></div>
          <div class="log-status"><?= $log['status'] ?></div>
        </div>
      <?php endforeach; ?>

    </div>

  </div>

</body>
</html>