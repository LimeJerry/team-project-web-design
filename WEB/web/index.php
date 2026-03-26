<?php
session_start();
include 'config.php';

$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (
    isset($_POST['id'], $_POST['pw']) &&
    $_POST['id'] === $ADMIN_ID &&
    $_POST['pw'] === $ADMIN_PW
  ) {
    $_SESSION['admin'] = true;
    header("Location: dashboard/cam_dashboard.php");
    exit;
  }

  $error = true;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>5분대기봇 관리자 로그인</title>

  <style>
    @font-face {
      font-family: 'NanumSquare';
      src: url('NanumSquareL.otf') format('opentype');
      font-weight: 300;
    }

    @font-face {
      font-family: 'NanumSquare';
      src: url('NanumSquareEB.otf') format('opentype');
      font-weight: 800;
    }

    :root {
      --bg: #edf3f7;
      --card: rgba(255, 255, 255, 0.88);
      --border: rgba(142, 185, 200, 0.7);
      --text: #163140;
      --muted: #587485;
      --accent: #135c76;
      --accent-strong: #0b475d;
      --danger: #bf3345;
    }

    * {
      box-sizing: border-box;
      font-family: 'NanumSquare', sans-serif;
    }

    html,
    body {
      margin: 0;
      min-height: 100%;
    }

    body {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 24px;
      color: var(--text);
      background:
        radial-gradient(circle at top left, rgba(19, 92, 118, 0.15), transparent 28%),
        linear-gradient(180deg, #f3f8fb 0%, #e8eff4 100%);
    }

    .login-shell {
      width: min(1040px, 100%);
      display: grid;
      grid-template-columns: minmax(300px, 1.1fr) minmax(320px, 0.9fr);
      border-radius: 30px;
      overflow: hidden;
      background: var(--card);
      border: 1px solid rgba(255, 255, 255, 0.55);
      box-shadow: 0 28px 60px rgba(8, 56, 74, 0.16);
      backdrop-filter: blur(14px);
    }

    .login-hero {
      padding: 44px 40px;
      color: #f7fcff;
      background: linear-gradient(145deg, #0b475d, #1c7592);
      position: relative;
      overflow: hidden;
    }

    .login-hero::after {
      content: '';
      position: absolute;
      inset: auto -40px -80px auto;
      width: 220px;
      height: 220px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      filter: blur(4px);
    }

    .hero-title {
      margin: 0;
      font-size: clamp(32px, 4vw, 44px);
      line-height: 1.08;
      letter-spacing: -0.04em;
    }

    .hero-copy {
      margin: 18px 0 0;
      max-width: 420px;
      color: rgba(247, 252, 255, 0.8);
      line-height: 1.65;
    }

    .hero-points {
      margin: 28px 0 0;
      padding: 0;
      list-style: none;
      display: grid;
      gap: 12px;
    }

    .hero-points li {
      padding: 12px 14px;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.09);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .login-panel {
      padding: 42px 34px;
      background: rgba(255, 255, 255, 0.78);
    }

    .panel-title {
      margin: 0;
      font-size: 28px;
      font-weight: 800;
      color: var(--accent);
    }

    .panel-copy {
      margin: 10px 0 0;
      color: var(--muted);
      line-height: 1.6;
    }

    .login-form {
      margin-top: 28px;
      display: grid;
      gap: 18px;
    }

    .input-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 800;
      color: var(--text);
    }

    .input-group input {
      width: 100%;
      padding: 15px 16px;
      border-radius: 16px;
      border: 1px solid var(--border);
      background: rgba(246, 251, 253, 0.95);
      color: var(--text);
      font-size: 15px;
      transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .input-group input[type='password'] {
      font-family: Arial, sans-serif;
      letter-spacing: 0.18em;
    }

    .input-group input:focus {
      outline: none;
      border-color: rgba(19, 92, 118, 0.8);
      background: #ffffff;
      box-shadow: 0 0 0 4px rgba(19, 92, 118, 0.1);
    }

    .login-btn {
      padding: 15px 18px;
      border: none;
      border-radius: 16px;
      background: linear-gradient(135deg, var(--accent), var(--accent-strong));
      color: #ffffff;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 0 16px 30px rgba(11, 71, 93, 0.18);
    }

    .login-btn:hover {
      filter: brightness(1.03);
    }

    .error {
      margin-top: 16px;
      padding: 12px 14px;
      border-radius: 14px;
      background: rgba(191, 51, 69, 0.08);
      border: 1px solid rgba(191, 51, 69, 0.18);
      color: var(--danger);
      font-size: 14px;
      font-weight: 800;
    }

    @media (max-width: 860px) {
      .login-shell {
        grid-template-columns: 1fr;
      }

      .login-hero,
      .login-panel {
        padding: 28px 22px;
      }
    }
  </style>
</head>
<body>
  <main class="login-shell">
    <section class="login-hero">
      <h1 class="hero-title">5분 대기봇 관리자 페이지</h1>
      <p class="hero-copy">실시간 감지 이미지, 운행 상태, 최근 로그를 하나의 대시보드에서 빠르게 확인하세요.</p>

      <ul class="hero-points">
        <li>각 층별 그리고 날자별로 이미지를 표시</li>
        <li>YOLO 이미지와 SLAM 맵을 나란히 비교</li>
        <li>로그인 후 모니터링 화면으로 바로 이동</li>
      </ul>
    </section>

    <section class="login-panel">
      <h2 class="panel-title">관리자 로그인</h2>
      <p class="panel-copy">등록된 관리자 계정으로 접속해 순찰 대시보드를 확인하세요.</p>

      <form method="post" class="login-form">
        <div class="input-group">
          <label for="id">관리자 ID</label>
          <input id="id" name="id" type="text" required>
        </div>

        <div class="input-group">
          <label for="pw">비밀번호</label>
          <input id="pw" name="pw" type="password" required>
        </div>

        <button class="login-btn" type="submit">로그인</button>
      </form>

      <?php if ($error): ?>
        <div class="error">잘못된 ID 또는 비밀번호입니다.</div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
