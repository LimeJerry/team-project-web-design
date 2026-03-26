<?php include 'header.php'; ?>

<?php
require_once __DIR__ . '/image_loader.php';

$imageDir = getConfiguredImageDirectory();
$files = getDetectionImageFiles();
$groups = parseDetectionGroups($files);
$sort = $_GET['sort'] ?? 'latest';
$order = $_GET['order'] ?? 'desc';

if (!in_array($sort, ['latest', 'floor'], true)) {
  $sort = 'latest';
}

if (!in_array($order, ['asc', 'desc'], true)) {
  $order = 'desc';
}

$entries = [];

foreach ($groups as $groupKey => $images) {
  $timestamp = $images['timestamp'];
  $floor = $images['floor'];
  $dt = DateTime::createFromFormat('Ymd_His', $timestamp);
  $entries[] = [
    'group_key' => $groupKey,
    'floor' => $floor,
    'timestamp' => $timestamp,
    'datetime' => $dt ? $dt->format('Y년 m월 d일 A g:i:s') : $timestamp,
    'time_only' => $dt ? $dt->format('A g:i분') : $timestamp,
    'date_only' => $dt ? $dt->format('Y-m-d') : '',
    'location' => $floor . ' 순찰 구역',
    'status' => '순찰 중',
    'battery' => '정보 수신 대기',
    'yolo' => isset($images['YOLO']) ? buildDetectionImageUrl($images['YOLO']) : null,
    'slam' => isset($images['SLAM']) ? buildDetectionImageUrl($images['SLAM']) : null,
  ];
}

$entryCount = count($entries);
$chronologicalEntries = $entries;
$latestEntry = $chronologicalEntries[0] ?? null;
$firstEntry = $entryCount > 0 ? $chronologicalEntries[$entryCount - 1] : null;
$boardStartedAt = $firstEntry ? $firstEntry['datetime'] : '감지 데이터 없음';
$dataSignature = buildDetectionImageSignature($files);

$floorBoards = [];

foreach ($chronologicalEntries as $entry) {
  $floor = $entry['floor'];

  if (!isset($floorBoards[$floor])) {
    $floorBoards[$floor] = [
      'floor' => $floor,
      'location' => $entry['location'],
      'status' => $entry['status'],
      'battery' => $entry['battery'],
      'latest_timestamp' => $entry['timestamp'],
      'latest_datetime' => $entry['datetime'],
      'captures' => [],
    ];
  }

  $floorBoards[$floor]['captures'][] = $entry;
}

foreach ($floorBoards as &$floorBoard) {
  usort($floorBoard['captures'], function (array $left, array $right): int {
    $comparison = strcmp($right['timestamp'], $left['timestamp']);

    if ($comparison !== 0) {
      return $comparison;
    }

    return strcmp($right['group_key'], $left['group_key']);
  });
}
unset($floorBoard);

$boardRows = array_values($floorBoards);

usort($boardRows, function (array $left, array $right) use ($sort, $order): int {
  if ($sort === 'floor') {
    preg_match('/\d+/', $left['floor'], $leftFloorMatch);
    preg_match('/\d+/', $right['floor'], $rightFloorMatch);
    $leftFloor = isset($leftFloorMatch[0]) ? (int)$leftFloorMatch[0] : 0;
    $rightFloor = isset($rightFloorMatch[0]) ? (int)$rightFloorMatch[0] : 0;
    $comparison = $leftFloor <=> $rightFloor;
  } else {
    $comparison = strcmp($left['latest_timestamp'], $right['latest_timestamp']);

    if ($comparison === 0) {
      preg_match('/\d+/', $left['floor'], $leftFloorMatch);
      preg_match('/\d+/', $right['floor'], $rightFloorMatch);
      $leftFloor = isset($leftFloorMatch[0]) ? (int)$leftFloorMatch[0] : 0;
      $rightFloor = isset($rightFloorMatch[0]) ? (int)$rightFloorMatch[0] : 0;
      $comparison = $leftFloor <=> $rightFloor;
    }
  }

  return $order === 'asc' ? $comparison : -$comparison;
});

foreach ($boardRows as $index => &$boardRow) {
  $boardRow['sequence'] = $index + 1;
}
unset($boardRow);
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

.board-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.sort-form {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.sort-select {
  min-width: 150px;
  padding: 10px 14px;
  border-radius: 999px;
  border: 1px solid rgba(131, 176, 191, 0.75);
  background: #ffffff;
  color: var(--surface-strong-2);
  font-weight: 800;
}

.board-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.board-row {
  display: grid;
  grid-template-columns: 200px minmax(0, 1fr);
  border: 1px solid rgba(131, 176, 191, 0.6);
  border-radius: 22px;
  overflow: hidden;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(247, 251, 253, 0.95));
}

.robot-panel {
  padding: 18px 16px;
  background: linear-gradient(180deg, #155a73, #0d4960);
  color: #f5fbff;
  border-right: 1px solid rgba(255, 255, 255, 0.12);
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.robot-name {
  font-size: 18px;
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
  gap: 10px;
}

.robot-stat {
  padding: 10px 12px;
  border-radius: 14px;
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
  font-size: 13px;
  font-weight: 800;
  line-height: 1.3;
}

.capture-panel {
  padding: 12px;
  background: linear-gradient(180deg, rgba(233, 242, 247, 0.56), rgba(255, 255, 255, 0.92));
}

.capture-rail {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: minmax(360px, calc((100% - 36px) / 4));
  gap: 12px;
  overflow-x: auto;
  padding-bottom: 8px;
  scrollbar-width: thin;
}

.capture-rail::-webkit-scrollbar {
  height: 10px;
}

.capture-rail::-webkit-scrollbar-thumb {
  background: rgba(20, 89, 116, 0.28);
  border-radius: 999px;
}

.capture-pair {
  background: #f8fcfe;
  border: 2px solid rgba(12, 62, 79, 0.75);
  border-radius: 22px;
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
}

.capture-pair-header {
  padding: 12px 14px;
  border-radius: 16px;
  background: #dcecf3;
  border: 1px solid rgba(131, 176, 191, 0.75);
  color: var(--surface-strong-2);
  font-size: 14px;
  font-weight: 800;
  text-align: center;
}

.capture-pair-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.capture-card {
  display: flex;
  flex-direction: column;
  gap: 8px;
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
  border-radius: 16px;
  overflow: hidden;
  background: linear-gradient(135deg, #d9eaf1, #edf6fa);
  border: 1px solid rgba(131, 176, 191, 0.75);
  aspect-ratio: 4 / 3;
  min-width: 0;
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

  .board-head {
    align-items: stretch;
    flex-direction: column;
  }

  .capture-rail {
    grid-auto-columns: minmax(280px, 82vw);
  }

  .capture-pair-grid {
    grid-template-columns: 1fr;
  }

  .capture-frame {
    aspect-ratio: 4 / 3;
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
    <div class="summary-copy">가장 최근에 수신된 감지 시각을 기준으로 보드를 구성</div>
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
      <p class="section-subtitle">층 정보와 기록 순서를 함께 표시하도록 파일명 규칙을 반영</p>
    </div>
    <div class="board-toolbar">
      <form class="sort-form" method="get">
        <select class="sort-select" name="sort" onchange="this.form.submit()">
          <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>최신 업데이트 순</option>
          <option value="floor" <?= $sort === 'floor' ? 'selected' : '' ?>>층별 정렬</option>
        </select>
        <select class="sort-select" name="order" onchange="this.form.submit()">
          <option value="desc" <?= $order === 'desc' ? 'selected' : '' ?>>내림차순</option>
          <option value="asc" <?= $order === 'asc' ? 'selected' : '' ?>>오름차순</option>
        </select>
      </form>
      <div class="board-meta">이미지 폴더: <?= htmlspecialchars($imageDir) ?></div>
    </div>
  </div>

  <?php if ($entryCount === 0): ?>
    <div class="empty-state">
      <h3>표시할 감지 데이터가 없습니다.</h3>
      <p>`dashboard/images` 폴더에 `1F_YYYYMMDD_HHMMSS_YOLO.jpg`, `1F_YYYYMMDD_HHMMSS_SLAM.jpg` 형식의 이미지가 생기면 자동으로 이 보드에 반영</p>
    </div>
  <?php else: ?>
    <div class="board-list">
      <?php foreach ($boardRows as $boardRow): ?>
        <section class="board-row">
          <aside class="robot-panel">
            <div class="robot-seq">Record <?= $boardRow['sequence'] ?></div>
            <div class="robot-name"><?= htmlspecialchars($boardRow['floor']) ?> 5분대기로봇</div>

            <div class="robot-stats">
              <div class="robot-stat">
                <span class="robot-stat-label">층 / 감지 쌍 수</span>
                <div class="robot-stat-value"><?= htmlspecialchars($boardRow['floor']) ?> / <?= count($boardRow['captures']) ?>쌍</div>
              </div>

              <div class="robot-stat">
                <span class="robot-stat-label">최신 감지 시각</span>
                <div class="robot-stat-value"><?= htmlspecialchars($boardRow['latest_datetime']) ?></div>
              </div>

              <div class="robot-stat">
                <span class="robot-stat-label">상태</span>
                <div class="robot-stat-value"><?= htmlspecialchars($boardRow['status']) ?></div>
              </div>

              <div class="robot-stat">
                <span class="robot-stat-label">위치</span>
                <div class="robot-stat-value"><?= htmlspecialchars($boardRow['location']) ?></div>
              </div>

              <div class="robot-stat">
                <span class="robot-stat-label">배터리</span>
                <div class="robot-stat-value"><?= htmlspecialchars($boardRow['battery']) ?></div>
              </div>
            </div>
          </aside>

          <div class="capture-panel">
            <div class="capture-rail">
              <?php foreach ($boardRow['captures'] as $capture): ?>
                <article class="capture-pair">
                  <div class="capture-pair-header"><?= htmlspecialchars($capture['floor']) ?> · <?= htmlspecialchars($capture['time_only']) ?></div>

                  <div class="capture-pair-grid">
                    <section class="capture-card">
                      <div class="capture-label">YOLO 이미지</div>
                      <div class="capture-frame">
                        <?php if ($capture['yolo']): ?>
                          <img src="<?= htmlspecialchars($capture['yolo']) ?>" alt="YOLO 감지 이미지">
                        <?php else: ?>
                          <div class="capture-empty">YOLO 이미지가 아직 없습니다.</div>
                        <?php endif; ?>
                      </div>
                    </section>

                    <section class="capture-card">
                      <div class="capture-label">SLAM 맵</div>
                      <div class="capture-frame">
                        <?php if ($capture['slam']): ?>
                          <img src="<?= htmlspecialchars($capture['slam']) ?>" alt="SLAM 맵 이미지">
                        <?php else: ?>
                          <div class="capture-empty">SLAM 이미지가 아직 없습니다.</div>
                        <?php endif; ?>
                      </div>
                    </section>
                  </div>
                </article>
              <?php endforeach; ?>
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
