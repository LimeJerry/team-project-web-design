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

/*
  ============================
  임시 데이터(나중에 DB로 교체)
  ============================
  - detectionLogs : 사람 감지 기록
  - systemLogs    : 시스템 이벤트 로그
  - robotStatus   : 상단 상태바 표시용
*/
$robotStatus = [
  "mode" => "순찰 중",
  "battery" => 81,       // %
  "detected_total" => 3, // 오늘/현재 세션 기준 등으로 사용
  "last_detected" => "14:42",
  "last_pos" => "복도 A-3 (예시)"
];

$detectionLogs = [
  [
    "time" => "14:42:12",
    "pos"  => "(3.2, 5.1)",
    "image"=> "../uploads/detect_144212.jpg", // 없으면 자동 placeholder
    "status" => "미확인"
  ],
  [
    "time" => "14:31:08",
    "pos"  => "(4.7, 2.8)",
    "image"=> "../uploads/detect_143108.jpg",
    "status" => "확인"
  ],
  [
    "time" => "14:20:33",
    "pos"  => "(1.9, 6.4)",
    "image"=> "../uploads/detect_142033.jpg",
    "status" => "미확인"
  ],
];

$systemLogs = [
  ["time" => "14:10:00", "msg" => "순찰 모드 시작"],
  ["time" => "14:18:12", "msg" => "SLAM 맵 업데이트 완료"],
  ["time" => "14:29:44", "msg" => "장애물 감지(거리 0.6m) - 감속"],
  ["time" => "14:40:05", "msg" => "배터리 상태 정상(81%)"],
];

// 최근 감지 요약(오른쪽 패널): 최신 N개
$summaryCount = 6;
$summaryDetections = array_slice($detectionLogs, 0, $summaryCount);

// 상단 상태바: 마지막 감지/총 감지 자동 계산(임시)
if (count($detectionLogs) > 0) {
  $robotStatus["last_detected"] = $detectionLogs[0]["time"];
  $robotStatus["detected_total"] = count($detectionLogs);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>로봇 관리자 대시보드</title>

  <style>
    * { box-sizing: border-box; font-family: "Segoe UI","Apple SD Gothic Neo",sans-serif; }
    body { margin:0; background:#F4FAFD; color:#1F2933; }

    /* ===== Header ===== */
    .header{
      background:#6EC1E4;
      padding:18px 32px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      border-bottom:1px solid #BFE3F2;
    }
    .header h1{ margin:0; font-size:20px; font-weight:600; }
    .logout{
      text-decoration:none;
      color:#fff;
      background:#4A9CC7;
      padding:8px 16px;
      border-radius:10px;
      font-size:14px;
      display:inline-flex;
      align-items:center;
      gap:8px;
    }
    .logout:hover{ background:#3B8BB3; }

    /* ===== Status Bar ===== */
    .statusbar{
      margin:18px 28px 0 28px;
      background:#fff;
      border:2px solid #BFE3F2;
      border-radius:16px;
      padding:14px 16px;
      display:flex;
      gap:12px;
      flex-wrap:wrap;
      align-items:center;
      justify-content:space-between;
    }
    .status-left{ display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .badge{
      background:#F7FCFF;
      border:1px solid #BFE3F2;
      color:#2B5D77;
      padding:8px 10px;
      border-radius:999px;
      font-size:13px;
      display:inline-flex;
      gap:8px;
      align-items:center;
      white-space:nowrap;
    }
    .badge strong{ font-weight:700; color:#1F2933; }
    .battery{
      display:flex; align-items:center; gap:10px;
      min-width:240px;
      justify-content:flex-end;
    }
    .battery-track{
      width:160px; height:10px; background:#EAF6FC;
      border:1px solid #BFE3F2;
      border-radius:999px; overflow:hidden;
    }
    .battery-fill{
      height:100%;
      width:0%;
      background:#6EC1E4; /* day-sky-blue 계열 */
    }
    .battery-text{ font-size:13px; color:#2B5D77; }

    /* ===== Layout ===== */
    .container{
      display:flex;
      gap:24px;
      padding:18px 28px 22px 28px;
      align-items:stretch;
    }

    /* ===== Boxes ===== */
    .box{
      background:#fff;
      border-radius:16px;
      padding:20px;
      border:2px solid #BFE3F2;
      min-width:0; /* flex overflow fix */
    }
    .section-title{
      font-size:16px;
      font-weight:600;
      color:#4A9CC7;
      margin-bottom:12px;
    }

    /* ===== Camera ===== */
    .camera-box{ flex:1.4; }
    .camera-stream{
      height:420px;
      border-radius:12px;
      background:#000;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#6EC1E4;
      font-size:15px;
      position:relative;
      overflow:hidden;
    }
    .camera-hint{
      position:absolute;
      left:12px; top:12px;
      background:rgba(255,255,255,0.10);
      border:1px solid rgba(255,255,255,0.18);
      color:#D6F2FF;
      padding:6px 10px;
      border-radius:10px;
      font-size:12px;
      backdrop-filter: blur(6px);
    }

    /* ===== Right Summary ===== */
    .summary-box{ flex:1; max-height:520px; overflow:auto; }
    .detect-item{
      padding:14px 16px;
      margin-bottom:12px;
      border-left:4px solid #6EC1E4;
      background:#F7FCFF;
      border-radius:10px;
    }
    .detect-time{ font-size:12px; color:#6B7280; margin-bottom:6px; }
    .detect-pos{ font-size:15px; font-weight:700; margin-bottom:4px; }
    .detect-status{
      margin-top:8px;
      font-size:12px;
      font-weight:700;
      display:inline-flex;
      padding:6px 10px;
      border-radius:999px;
      border:1px solid #BFE3F2;
      background:#fff;
      color:#2B5D77;
    }
    .detect-status.pending{ color:#8A5A00; border-color:#F2D59B; background:#FFF7E6; }
    .detect-status.done{ color:#1F5F7A; border-color:#BFE3F2; background:#F0FBFF; }

    /* ===== Tabs Area ===== */
    .tabs-wrap{
      padding:0 28px 28px 28px;
    }
    .tabs-box{
      background:#fff;
      border:2px solid #BFE3F2;
      border-radius:16px;
      overflow:hidden;
    }
    .tabs{
      display:flex;
      gap:6px;
      padding:10px;
      background:#F7FCFF;
      border-bottom:1px solid #BFE3F2;
    }
    .tab-btn{
      border:none;
      background:transparent;
      padding:10px 14px;
      border-radius:12px;
      cursor:pointer;
      font-size:14px;
      font-weight:700;
      color:#2B5D77;
    }
    .tab-btn.active{
      background:#6EC1E4;
      color:#fff;
    }
    .tab-content{
      display:none;
      padding:18px;
    }
    .tab-content.active{ display:block; }

    /* ===== Table ===== */
    table{ width:100%; border-collapse:separate; border-spacing:0 10px; }
    th{
      text-align:left;
      font-size:12px;
      color:#6B7280;
      font-weight:700;
      padding:0 10px 4px 10px;
    }
    td{
      background:#F7FCFF;
      border:1px solid #BFE3F2;
      padding:12px 10px;
      font-size:13px;
      color:#1F2933;
    }
    tr td:first-child{ border-radius:12px 0 0 12px; }
    tr td:last-child{ border-radius:0 12px 12px 0; }

    .thumb{
      width:70px; height:44px;
      border-radius:10px;
      object-fit:cover;
      border:1px solid #BFE3F2;
      background:#EAF6FC;
      cursor:pointer;
    }
    .pill{
      display:inline-flex;
      padding:6px 10px;
      border-radius:999px;
      font-weight:800;
      font-size:12px;
      border:1px solid #BFE3F2;
      background:#fff;
      color:#2B5D77;
    }
    .pill.pending{ color:#8A5A00; border-color:#F2D59B; background:#FFF7E6; }
    .pill.done{ color:#1F5F7A; border-color:#BFE3F2; background:#F0FBFF; }

    /* ===== Modal ===== */
    .modal{
      display:none;
      position:fixed;
      left:0; top:0; right:0; bottom:0;
      background:rgba(0,0,0,0.55);
      align-items:center;
      justify-content:center;
      padding:20px;
      z-index:9999;
    }
    .modal.active{ display:flex; }
    .modal-card{
      width:min(980px, 96vw);
      background:#fff;
      border-radius:16px;
      border:2px solid #BFE3F2;
      overflow:hidden;
    }
    .modal-head{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:12px 14px;
      background:#F7FCFF;
      border-bottom:1px solid #BFE3F2;
    }
    .modal-title{
      font-size:14px;
      font-weight:800;
      color:#2B5D77;
    }
    .modal-close{
      border:none;
      background:#4A9CC7;
      color:#fff;
      font-weight:800;
      padding:8px 12px;
      border-radius:10px;
      cursor:pointer;
    }
    .modal-close:hover{ background:#3B8BB3; }
    .modal-body{ padding:14px; }
    .modal-img{
      width:100%;
      max-height:70vh;
      object-fit:contain;
      background:#000;
      border-radius:12px;
    }

    /* ===== Responsive ===== */
    @media (max-width: 1050px){
      .container{ flex-direction:column; }
      .summary-box{ max-height:none; }
      .battery{ justify-content:flex-start; min-width:auto; }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="header">
    <h1>로봇 관리자 대시보드</h1>
    <a href="dashboard.php?logout=1" class="logout">로그아웃</a>
  </div>

  <!-- Status Bar -->
  <div class="statusbar">
    <div class="status-left">
      <div class="badge">모드 <strong><?= htmlspecialchars($robotStatus["mode"]) ?></strong></div>
      <div class="badge">감지 인원 <strong><?= (int)$robotStatus["detected_total"] ?></strong></div>
      <div class="badge">마지막 감지 <strong><?= htmlspecialchars($robotStatus["last_detected"]) ?></strong></div>
      <div class="badge">최근 위치 <strong><?= htmlspecialchars($robotStatus["last_pos"]) ?></strong></div>
    </div>

    <div class="battery" aria-label="Battery status">
      <div class="battery-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int)$robotStatus["battery"] ?>">
        <div class="battery-fill" id="batteryFill"></div>
      </div>
      <div class="battery-text">배터리 <strong><?= (int)$robotStatus["battery"] ?>%</strong></div>
    </div>
  </div>

  <!-- Main -->
  <div class="container">

    <!-- Camera -->
    <div class="box camera-box">
      <div class="section-title">라이브 카메라</div>
      <div class="camera-stream">
        <div class="camera-hint">실시간 스트림 연결 전 (예시)</div>
        Camera Stream
        <!-- 실제 연결 시: <img src="..." /> 또는 <video ...> 형태로 교체 -->
      </div>
    </div>

    <!-- Right Summary -->
    <div class="box summary-box">
      <div class="section-title">사람 감지 위치 요약 (최근 <?= (int)$summaryCount ?>건)</div>

      <?php if (count($summaryDetections) === 0): ?>
        <div class="detect-item">
          <div class="detect-pos">감지 기록 없음</div>
          <div class="detect-time">현재까지 저장된 감지 이벤트가 없습니다.</div>
        </div>
      <?php else: ?>
        <?php foreach ($summaryDetections as $d): ?>
          <?php
            $isDone = ($d["status"] === "확인");
            $statusClass = $isDone ? "done" : "pending";
          ?>
          <div class="detect-item">
            <div class="detect-time"><?= htmlspecialchars($d["time"]) ?></div>
            <div class="detect-pos">좌표: <?= htmlspecialchars($d["pos"]) ?></div>
            <span class="detect-status <?= $statusClass ?>"><?= htmlspecialchars($d["status"]) ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>

  </div>

  <!-- Tabs -->
  <div class="tabs-wrap">
    <div class="tabs-box">
      <div class="tabs">
        <button class="tab-btn active" data-tab="tab-detect" type="button">감지 기록</button>
        <button class="tab-btn" data-tab="tab-system" type="button">시스템 로그</button>
      </div>

      <!-- Tab: Detection Records -->
      <div class="tab-content active" id="tab-detect">
        <div class="section-title" style="margin-bottom:10px;">감지된 사람의 위치 / 시간 / 사진</div>

        <?php if (count($detectionLogs) === 0): ?>
          <div class="detect-item">
            <div class="detect-pos">감지 기록 없음</div>
            <div class="detect-time">사람 감지 이벤트가 저장되면 이 탭에 표시됩니다.</div>
          </div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th style="width:90px;">사진</th>
                <th style="width:140px;">시간</th>
                <th style="width:180px;">좌표</th>
                <th style="width:120px;">상태</th>
                <th>파일 경로(참고)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($detectionLogs as $d): ?>
                <?php
                  $isDone = ($d["status"] === "확인");
                  $statusClass = $isDone ? "done" : "pending";

                  // 이미지가 실제로 없을 수 있으니 placeholder 제공
                  $imgPath = $d["image"];
                  $safeImg = htmlspecialchars($imgPath);
                  $placeholder = "data:image/svg+xml;charset=UTF-8," . rawurlencode(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200">
                      <rect width="100%" height="100%" fill="#0b0b0b"/>
                      <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#6EC1E4" font-size="18" font-family="Segoe UI, sans-serif">
                        No Image
                      </text>
                    </svg>'
                  );
                ?>
                <tr>
                  <td>
                    <img
                      class="thumb"
                      src="<?= $safeImg ?>"
                      onerror="this.onerror=null; this.src='<?= $placeholder ?>';"
                      alt="감지 캡처"
                      data-full="<?= $safeImg ?>"
                      data-time="<?= htmlspecialchars($d["time"]) ?>"
                      data-pos="<?= htmlspecialchars($d["pos"]) ?>"
                    >
                  </td>
                  <td><?= htmlspecialchars($d["time"]) ?></td>
                  <td><?= htmlspecialchars($d["pos"]) ?></td>
                  <td><span class="pill <?= $statusClass ?>"><?= htmlspecialchars($d["status"]) ?></span></td>
                  <td style="word-break:break-all; font-size:12px; color:#6B7280;"><?= $safeImg ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div style="margin-top:10px; font-size:12px; color:#6B7280;">
            사진을 클릭하면 크게 볼 수 있습니다.
          </div>
        <?php endif; ?>
      </div>

      <!-- Tab: System Logs -->
      <div class="tab-content" id="tab-system">
        <div class="section-title" style="margin-bottom:10px;">순찰/SLAM/배터리 등 시스템 이벤트</div>

        <?php if (count($systemLogs) === 0): ?>
          <div class="detect-item">
            <div class="detect-pos">시스템 로그 없음</div>
            <div class="detect-time">시스템 이벤트가 저장되면 이 탭에 표시됩니다.</div>
          </div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th style="width:160px;">시간</th>
                <th>내용</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($systemLogs as $s): ?>
                <tr>
                  <td><?= htmlspecialchars($s["time"]) ?></td>
                  <td><?= htmlspecialchars($s["msg"]) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Image Modal -->
  <div class="modal" id="imgModal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-card">
      <div class="modal-head">
        <div class="modal-title" id="modalTitle">감지 이미지</div>
        <button class="modal-close" id="modalClose" type="button">닫기</button>
      </div>
      <div class="modal-body">
        <img class="modal-img" id="modalImg" src="" alt="감지 원본 이미지">
      </div>
    </div>
  </div>

  <script>
    // 배터리 바 채우기
    (function(){
      const battery = <?= (int)$robotStatus["battery"] ?>;
      const fill = document.getElementById("batteryFill");
      fill.style.width = Math.max(0, Math.min(100, battery)) + "%";
    })();

    // 탭 전환
    document.querySelectorAll(".tab-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
        document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
        btn.classList.add("active");
        document.getElementById(btn.dataset.tab).classList.add("active");
      });
    });

    // 이미지 모달
    const modal = document.getElementById("imgModal");
    const modalImg = document.getElementById("modalImg");
    const modalTitle = document.getElementById("modalTitle");
    const modalClose = document.getElementById("modalClose");

    function openModal(imgSrc, time, pos){
      modal.classList.add("active");
      modal.setAttribute("aria-hidden", "false");
      modalImg.src = imgSrc;
      modalTitle.textContent = `감지 이미지 - ${time} / ${pos}`;
    }
    function closeModal(){
      modal.classList.remove("active");
      modal.setAttribute("aria-hidden", "true");
      modalImg.src = "";
    }

    document.addEventListener("click", (e) => {
      const t = e.target;
      if (t.classList.contains("thumb")) {
        const src = t.getAttribute("data-full") || t.src;
        openModal(src, t.getAttribute("data-time") || "", t.getAttribute("data-pos") || "");
      }
      if (t === modal) closeModal();
    });

    modalClose.addEventListener("click", closeModal);
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeModal();
    });
  </script>

</body>
</html>