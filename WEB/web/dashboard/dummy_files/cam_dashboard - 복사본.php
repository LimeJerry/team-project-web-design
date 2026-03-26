<?php include 'header.php'; ?>

<?php
// ✅ 이미지 확장자 폭넓게
$files = glob("images/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
rsort($files);

// timestamp(YYYYMMDD_HHMMSS) 기준으로 YOLO/SLAM 페어 묶기
$groups = [];

foreach ($files as $file) {
  $filename = basename($file);

  // 예: 20260227_103030_YOLO.jpg / 20260227_103030_SLAM.png
  if (preg_match('/(\d{8}_\d{6})_(YOLO|SLAM)\.(jpg|jpeg|png)$/i', $filename, $m)) {
    $timestamp = $m[1];
    $type = strtoupper($m[2]);

    if (!isset($groups[$timestamp])) $groups[$timestamp] = [];
    $groups[$timestamp][$type] = $filename; // ✅ 파일명만 저장 (인코딩 처리용)
  }
}

// ✅ 최신 시간 먼저
krsort($groups);
?>

<style>
/* ✅ 핵심: min-width 제거 + 반응형 */
.dashboard-flex{
  display:flex;
  gap:24px;
  align-items: stretch;
}

/* 좁아지면 세로로 쌓이게 */
@media (max-width: 1100px) {
  .dashboard-flex{ flex-direction: column; }
}

/* 좌/우 패널 비율 */
.camera-area { flex: 1.8; min-width: 0; }
.side-panel  { flex: 1.0; min-width: 0; }

.video-wrapper{
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9; /* ✅ 16:9 고정 */
  background: #111;
  border-radius: 12px;
  overflow: hidden;
}

.video-wrapper video{
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  background: #111;
}

/* 오른쪽 패널: 세로 스크롤 */
.side-panel{
  background:white;
  border-radius:16px;
  padding:20px;
  border:2px solid #BFE3F2;

  max-height: 560px;   /* ✅ 필요하면 숫자만 조절 */
  overflow-y: auto;
  overflow-x: hidden;
}

/* 카드 리스트 */
.detect-grid{
  display:flex;
  flex-direction:column;
  gap:16px;
}

/* 카드 */
.detect-card{
  background:#F7FCFF;
  border:1px solid #BFE3F2;
  border-radius:12px;
  padding:14px;
}

/* ✅ 2장 가로 배치: 절대 밖으로 안 튀게 */
.image-row{
  display:flex;
  gap:12px;
  width:100%;
}

.thumb-wrap{
  flex: 1 1 0;
  min-width: 0;
  border-radius: 10px;
  overflow: hidden;
  background: #eef6fb;
  aspect-ratio: 16 / 9; /* ✅ 썸네일도 안정적인 비율 */
}

.thumb-wrap img{
  width:100%;
  height:100%;
  object-fit: cover;
  display:block;
}

/* 시간 텍스트 */
.info-line{
  font-size:14px;
  margin-top:10px;
  color:#374151;
  font-weight: 600;
}

.toast{
  position: fixed;
  bottom: 30px;
  right: 30px;
  background:#4A9CC7;
  color:white;
  padding:15px 20px;
  border-radius:12px;
  opacity:0;
  transition:0.4s;
  z-index:9999;
}

.toast.show{
  opacity:1;
}

</style>

<div class="container">
  <div class="dashboard-flex">

    <!-- 왼쪽: 라이브 카메라 -->
    <div class="box camera-area">
      <div class="section-title">라이브 카메라</div>

      <div class="video-wrapper">
        <video controls autoplay muted loop>
          <source src="" type="video/mp4">
          브라우저가 video 태그를 지원하지 않습니다.
        </video>
      </div>
    </div>

    <!-- 오른쪽: 최근 감지 -->
    <div class="side-panel">
      <div class="section-title">최근 감지 위치</div>

      <div class="detect-grid">
        <?php foreach ($groups as $timestamp => $images):

          // ✅ 표시용 시간 HH:MM:SS
          $time = substr($timestamp, 9, 2) . ":" . substr($timestamp, 11, 2) . ":" . substr($timestamp, 13, 2);

          // ✅ 파일명에 공백/괄호 있어도 안전하게 로딩
          $yoloSrc = isset($images['YOLO']) ? ("images/" . rawurlencode($images['YOLO'])) : null;
          $slamSrc = isset($images['SLAM']) ? ("images/" . rawurlencode($images['SLAM'])) : null;

          // 둘 중 하나라도 있어야 카드 생성
          if (!$yoloSrc && !$slamSrc) continue;
        ?>
          <div class="detect-card">
            <div class="image-row">
              <?php if ($yoloSrc): ?>
                <div class="thumb-wrap">
                  <img src="<?= $yoloSrc ?>" alt="YOLO">
                </div>
              <?php endif; ?>

              <?php if ($slamSrc): ?>
                <div class="thumb-wrap">
                  <img src="<?= $slamSrc ?>" alt="SLAM">
                </div>
              <?php endif; ?>
            </div>

            <div class="info-line"><?= htmlspecialchars($time) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<!-- 🔔 실시간 감지 스크립트 -->
<script>
let lastImage = null;
let updateAvailable = false;

function showPopup(msg){
    const toast = document.getElementById("toast");
    toast.innerHTML = `
        ${msg}
        <button onclick="reloadPage()" style="
            margin-left:15px;
            padding:5px 10px;
            border:none;
            border-radius:6px;
            background:white;
            color:#4A9CC7;
            cursor:pointer;
        ">새로고침</button>
    `;
    toast.classList.add("show");
}

function reloadPage(){
    location.reload();
}

function checkNewImage() {

    if(updateAvailable) return; 
    // 이미 팝업 떠있으면 더 이상 체크 안함

    fetch('check_new_image.php')
        .then(res => res.json())
        .then(data => {

            if (!data.latest) return;

            if (lastImage === null) {
                lastImage = data.latest;
                return; 
            }

            if (lastImage !== data.latest) {

                updateAvailable = true;
                showPopup("새로운 감지 이미지 도착");
            }
        });
}

setInterval(checkNewImage, 3000);
</script>

<div id="toast" class="toast"></div>

</body>
</html>