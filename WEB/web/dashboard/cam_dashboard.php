<?php include 'header.php'; ?>

<?php
$imageDir = __DIR__ . DIRECTORY_SEPARATOR . 'images';
$files = glob($imageDir . DIRECTORY_SEPARATOR . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
$files = $files ?: [];
rsort($files);

$groups = [];

foreach ($files as $file) {
  $filename = basename($file);

  if (preg_match('/([A-Z0-9]+F)_(\d{8}_\d{6})_(YOLO|SLAM)\.(jpg|jpeg|png)$/i', $filename, $matches)) {
    $floor = strtoupper($matches[1]);
    $timestamp = $matches[2];
    $type = strtoupper($matches[3]);
    $groupKey = $floor . '_' . $timestamp;

    if (!isset($groups[$groupKey])) {
      $groups[$groupKey] = [
        'floor' => $floor,
        'timestamp' => $timestamp,
      ];
    }

    $groups[$groupKey][$type] = $filename;
  }
}

krsort($groups);

$entries = [];
$index = 0;

foreach ($groups as $groupKey => $images) {
  $timestamp = $images['timestamp'];
  $floor = $images['floor'];
  $dt = DateTime::createFromFormat('Ymd_His', $timestamp);
  $entries[] = [
    'sequence' => ++$index,
    'group_key' => $groupKey,
    'floor' => $floor,
    'timestamp' => $timestamp,
    'datetime' => $dt ? $dt->format('Y년 m월 d일 A g:i:s') : $timestamp,
    'time_only' => $dt ? $dt->format('A g:i분') : $timestamp,
    'date_only' => $dt ? $dt->format('Y-m-d') : '',
    'location' => $floor . ' 순찰 구역',
    'status' => '순찰 중',
    'battery' => '정보 수신 대기',
    'yolo' => isset($images['YOLO']) ? ('images/' . rawurlencode($images['YOLO'])) : null,
    'slam' => isset($images['SLAM']) ? ('images/' . rawurlencode($images['SLAM'])) : null,
  ];
}

$entryCount = count($entries);
$latestEntry = $entries[0] ?? null;
$boardStartedAt = $latestEntry ? $latestEntry['datetime'] : '감지 데이터 없음';
$dataSignatureParts = [];

foreach ($files as $file) {
  $dataSignatureParts[] = basename($file) . ':' . @filemtime($file);
}

$dataSignature = md5(implode('|', $dataSignatureParts));
?>

<style>
.dashboard-summary {
  display: grid;
  grid-template-columns: minmax(0, 1.6fr) repeat(3, minmax(180px, 1fr));
  gap: 14px;
  margin-bottom: 20px;
}

.summary-hero,
.summary-chip {
  padding: 18px 20px;
  border-radius: 20px;
  border: 1px solid rgba(142, 185, 200, 0.65);
}

.summary-hero {
  background: linear-gradient(135deg, #114f67, #1d7694);
  color: #f7fcff;
}

.summary-eyebrow {
  font-size: 12px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  opacity: 0.72;
}

.summary-time {
  margin-top: 10px;
  font-size: clamp(22px, 2vw, 30px);
  font-weight: 800;
  line-height: 1.2;
}

.summary-copy {
  margin-top: 8px;
  font-size: 14px;
  color: rgba(247, 252, 255, 0.8);
}

.summary-chip {
  background: linear-gradient(180deg, #ffffff, #f6fbfd);
}

.summary-label {
  font-size: 13px;
  color: var(--muted);
}

.summary-value {
  margin-top: 10px;
  font-size: 24px;
  font-weight: 800;
  color: var(--surface-strong);
}

.summary-note {
  margin-top: 6px;
  font-size: 13px;
  color: var(--muted);
}

.board-shell {
  padding: 18px;
}

.board-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 18px;
}

.board-meta {
  color: var(--muted);
  font-size: 14px;
}

.board-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.board-row {
  display: grid;
  grid-template-columns: 260px minmax(0, 1fr);
  border: 1px solid rgba(131, 176, 191, 0.6);
  border-radius: 22px;
  overflow: hidden;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(247, 251, 253, 0.95));
}

.robot-panel {
  padding: 22px 20px;
  background: linear-gradient(180deg, #155a73, #0d4960);
  color: #f5fbff;
  border-right: 1px solid rgba(255, 255, 255, 0.12);
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.robot-name {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.03em;
}

.robot-seq {
  display: inline-flex;
  align-items: center;
  width: fit-content;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.12);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.robot-stats {
  display: grid;
  gap: 12px;
}

.robot-stat {
  padding: 12px 14px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.robot-stat-label {
  display: block;
  margin-bottom: 6px;
  font-size: 12px;
  color: rgba(245, 251, 255, 0.72);
}

.robot-stat-value {
  font-size: 16px;
  font-weight: 800;
}

.capture-panel {
  padding: 18px;
  background: linear-gradient(180deg, rgba(233, 242, 247, 0.56), rgba(255, 255, 255, 0.92));
}

.capture-header {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 12px;
}

.capture-timestamp {
  padding: 12px 16px;
  border-radius: 16px;
  background: #dcecf3;
  border: 1px solid rgba(131, 176, 191, 0.75);
  color: var(--surface-strong-2);
  text-align: center;
  font-weight: 800;
}

.capture-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.capture-card {
  background: #f8fcfe;
  border: 2px solid rgba(12, 62, 79, 0.75);
  border-radius: 22px;
  padding: 12px;
  min-height: 260px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
}

.capture-label {
  padding: 10px 12px;
  border-radius: 14px;
  background: linear-gradient(180deg, #145974, #0e4b61);
  color: #ffffff;
  font-size: 14px;
  font-weight: 800;
  text-align: center;
}

.capture-frame {
  position: relative;
  flex: 1;
  border-radius: 16px;
  overflow: hidden;
  background: linear-gradient(135deg, #d9eaf1, #edf6fa);
  border: 1px solid rgba(131, 176, 191, 0.75);
  min-height: 180px;
}

.capture-frame img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.capture-empty {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  text-align: center;
  color: var(--muted);
  font-weight: 800;
}

.empty-state {
  padding: 48px 24px;
  border-radius: 22px;
  background: linear-gradient(180deg, #ffffff, #f5fafc);
  border: 1px dashed rgba(131, 176, 191, 0.9);
  text-align: center;
}

.empty-state h3 {
  margin: 0;
  font-size: 22px;
  color: var(--surface-strong);
}

.empty-state p {
  margin: 10px 0 0;
  color: var(--muted);
}

.toast {
  position: fixed;
  right: 26px;
  bottom: 26px;
  max-width: 360px;
  padding: 16px 18px;
  border-radius: 18px;
  background: rgba(8, 56, 74, 0.96);
  color: #ffffff;
  box-shadow: 0 20px 45px rgba(8, 56, 74, 0.32);
  transform: translateY(20px);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.25s ease, transform 0.25s ease;
  z-index: 9999;
}

.toast.show {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

.toast button {
  margin-top: 12px;
  border: none;
  background: #ffffff;
  color: var(--surface-strong-2);
  font-weight: 800;
  border-radius: 12px;
  padding: 10px 12px;
  cursor: pointer;
}

@media (max-width: 1200px) {
  .dashboard-summary {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .summary-hero {
    grid-column: 1 / -1;
  }

  .board-row {
    grid-template-columns: 1fr;
  }

  .robot-panel {
    border-right: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  }
}

@media (max-width: 760px) {
  .dashboard-summary {
    grid-template-columns: 1fr;
  }

  .capture-header,
  .capture-grid {
    grid-template-columns: 1fr;
  }

  .board-shell {
    padding: 14px;
  }

  .robot-name {
    font-size: 20px;
  }
}
</style>

<div class="dashboard-summary">
  <div class="summary-hero">
    <div class="summary-eyebrow">Mission Overview</div>
    <div class="summary-time"><?= htmlspecialchars($boardStartedAt) ?></div>
    <div class="summary-copy">가장 최근에 수신된 감지 시각을 기준으로 보드를 구성했습니다.</div>
  </div>

  <div class="summary-chip">
    <div class="summary-label">감지 묶음 수</div>
    <div class="summary-value"><?= $entryCount ?></div>
    <div class="summary-note">YOLO 또는 SLAM 이미지가 존재하는 기록 기준</div>
  </div>

  <div class="summary-chip">
    <div class="summary-label">최신 감지</div>
    <div class="summary-value"><?= htmlspecialchars($latestEntry['time_only'] ?? '-') ?></div>
    <div class="summary-note">
      <?php if ($latestEntry): ?>
        <?= htmlspecialchars($latestEntry['floor']) ?> / <?= htmlspecialchars($latestEntry['date_only']) ?>
      <?php else: ?>
        날짜 정보 없음
      <?php endif; ?>
    </div>
  </div>

  <div class="summary-chip">
    <div class="summary-label">상태</div>
    <div class="summary-value">순찰 중</div>
    <div class="summary-note">신규 이미지 감지 시 알림을 표시합니다.</div>
  </div>
</div>

<div class="section-card board-shell">
  <div class="board-head">
    <div>
      <h2 class="section-title">실시간 감지 보드</h2>
      <p class="section-subtitle">층 정보와 기록 순서를 함께 표시하도록 파일명 규칙을 반영했습니다.</p>
    </div>
    <div class="board-meta">정렬 기준: 최신 기록 우선</div>
  </div>

  <?php if ($entryCount === 0): ?>
    <div class="empty-state">
      <h3>표시할 감지 데이터가 없습니다.</h3>
      <p>`dashboard/images` 폴더에 `1F_YYYYMMDD_HHMMSS_YOLO.jpg`, `1F_YYYYMMDD_HHMMSS_SLAM.jpg` 형식의 이미지가 생기면 자동으로 이 보드에 반영됩니다.</p>
    </div>
  <?php else: ?>
    <div class="board-list">
      <?php foreach ($entries as $entry): ?>
        <section class="board-row">
          <aside class="robot-panel">
            <div class="robot-seq">Record <?= $entry['sequence'] ?></div>
            <div class="robot-name"><?= htmlspecialchars($entry['floor']) ?> 순찰 로봇</div>

            <div class="robot-stats">
              <div class="robot-stat">
                <span class="robot-stat-label">층 / 순서쌍</span>
                <div class="robot-stat-value"><?= htmlspecialchars($entry['floor']) ?> / <?= $entry['sequence'] ?>번 기록</div>
              </div>

              <div class="robot-stat">
                <span class="robot-stat-label">감지 시각</span>
                <div class="robot-stat-value"><?= htmlspecialchars($entry['datetime']) ?></div>
              </div>

              <div class="robot-stat">
                <span class="robot-stat-label">상태</span>
                <div class="robot-stat-value"><?= htmlspecialchars($entry['status']) ?></div>
              </div>

              <div class="robot-stat">
                <span class="robot-stat-label">위치</span>
                <div class="robot-stat-value"><?= htmlspecialchars($entry['location']) ?></div>
              </div>

              <div class="robot-stat">
                <span class="robot-stat-label">배터리</span>
                <div class="robot-stat-value"><?= htmlspecialchars($entry['battery']) ?></div>
              </div>
            </div>
          </aside>

          <div class="capture-panel">
            <div class="capture-header">
              <div class="capture-timestamp"><?= htmlspecialchars($entry['floor']) ?> · <?= htmlspecialchars($entry['time_only']) ?> · YOLO 분석</div>
              <div class="capture-timestamp"><?= htmlspecialchars($entry['floor']) ?> · <?= htmlspecialchars($entry['time_only']) ?> · SLAM 맵</div>
            </div>

            <div class="capture-grid">
              <article class="capture-card">
                <div class="capture-label">YOLO 이미지</div>
                <div class="capture-frame">
                  <?php if ($entry['yolo']): ?>
                    <img src="<?= htmlspecialchars($entry['yolo']) ?>" alt="YOLO 감지 이미지">
                  <?php else: ?>
                    <div class="capture-empty">YOLO 이미지가 아직 없습니다.</div>
                  <?php endif; ?>
                </div>
              </article>

              <article class="capture-card">
                <div class="capture-label">SLAM 맵</div>
                <div class="capture-frame">
                  <?php if ($entry['slam']): ?>
                    <img src="<?= htmlspecialchars($entry['slam']) ?>" alt="SLAM 맵 이미지">
                  <?php else: ?>
                    <div class="capture-empty">SLAM 이미지가 아직 없습니다.</div>
                  <?php endif; ?>
                </div>
              </article>
            </div>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
let lastImage = null;
let updateAvailable = false;
let currentSignature = <?= json_encode($dataSignature, JSON_UNESCAPED_UNICODE) ?>;

function showPopup(message) {
  const toast = document.getElementById('toast');
  toast.innerHTML = `
    <div>${message}</div>
    <button type="button" onclick="reloadPage()">새로고침</button>
  `;
  toast.classList.add('show');
}

function reloadPage() {
  location.reload();
}

function checkNewImage() {
  fetch('check_new_image.php')
    .then(response => response.json())
    .then(data => {
      if (!data || !data.signature) {
        return;
      }

      if (lastImage === null) {
        lastImage = data.latest || null;
      }

      if (currentSignature === data.signature) {
        lastImage = data.latest || null;
        return;
      }

      currentSignature = data.signature;
      lastImage = data.latest || null;
      updateAvailable = true;

      const sound = document.getElementById('alertSound');
      if (sound) {
        sound.currentTime = 0;
        sound.play().catch(() => {});
      }

      showPopup('새 감지 이미지 반영 중');
      setTimeout(reloadPage, 1200);
    })
    .catch(() => {});
}

setInterval(checkNewImage, 3000);

document.addEventListener('click', function enableAudio() {
  const sound = document.getElementById('alertSound');
  if (sound) {
    sound.play().then(() => {
      sound.pause();
      sound.currentTime = 0;
    }).catch(() => {});
  }

  document.removeEventListener('click', enableAudio);
});
</script>

<div id="toast" class="toast"></div>

<audio id="alertSound" preload="auto">
  <source src="sounds/alert.mp3" type="audio/mpeg">
</audio>

</div>
</body>
</html>
