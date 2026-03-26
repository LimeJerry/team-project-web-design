<?php include 'header.php'; ?>

<?php
$files = glob("images/*.jpg");
rsort($files);

$groups = [];

foreach ($files as $file) {

    $filename = basename($file);

    // 파일명 패턴: YYYYMMDD_HHMMSS_YOLO.jpg 또는 _SLAM.jpg
    if (preg_match('/(\d{8}_\d{6})_(YOLO|SLAM)\.jpg$/i', $filename, $matches)) {

        $timestamp = $matches[1];
        $type = strtoupper($matches[2]);

        if (!isset($groups[$timestamp])) {
            $groups[$timestamp] = [];
        }

        $groups[$timestamp][$type] = $file;
    }
}
?>

<style>
.dashboard-flex { display:flex; gap:24px; }

.camera-area { flex:2; }

.side-panel {
  flex:1;
  background:white;
  border-radius:16px;
  padding:20px;
  border:2px solid #BFE3F2;
  display:flex;
  flex-direction:column;
}

.video-wrapper{
  background:black;
  border-radius:12px;
  overflow:hidden;
}

video{
  width:100%;
  height:420px;
  object-fit:cover;
  background:black;
}

.detect-grid{
  display:flex;
  flex-direction:column;
  gap:16px;
}

.detect-card{
  background:#F7FCFF;
  border:1px solid #BFE3F2;
  border-radius:12px;
  padding:16px;
}

.detect-thumb{
  flex:1 1 0;
  min-width:0;
  height:110px;
  object-fit:cover;
  border-radius:10px;
  width:100%;
}

.side-panel{
  max-height:500px;
  overflow-y:auto;
  overflow-x:hidden;
}

.info-line{
  font-size:13px;
  margin-bottom:4px;
}

.image-row{
    display:flex;
    gap:10px;
    width:100%;
}

.live-camera{
    position:relative;
    width:100%;
    padding-top:56.25%;  /* 16:9 비율 */
}

.live-camera video{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    object-fit:cover;
}

</style>

<div class="container">

  <div class="dashboard-flex">

    <div class="box camera-area">
      <div class="section-title">라이브 카메라</div>

      <div class="video-wrapper">
        <video controls autoplay muted loop>
          <source src="" type="video/mp4">
          브라우저가 video 태그를 지원하지 않습니다.
        </video>
      </div>
    </div>

    <div class="side-panel">
      <div class="section-title">최근 감지 위치</div>

      <div class="detect-grid">
        <?php foreach ($groups as $timestamp => $images): 

            $time = substr($timestamp, 9, 2) . ":" .
                    substr($timestamp, 11, 2) . ":" .
                    substr($timestamp, 13, 2);
        ?>
          <div class="detect-card">

              <div class="image-row">
                  <?php if (isset($images['YOLO'])): ?>
                      <img class="detect-thumb" src="<?= $images['YOLO'] ?>" alt="YOLO">
                  <?php endif; ?>

                  <?php if (isset($images['SLAM'])): ?>
                      <img class="detect-thumb" src="<?= $images['SLAM'] ?>" alt="SLAM">
                  <?php endif; ?>
              </div>

              <div class="info-line"><?= $time ?></div>

          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

</body>
</html>