<?php include 'header.php'; ?>

<?php
require_once __DIR__ . '/image_loader.php';

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

$captures = [];

foreach ($groups as $groupKey => $images) {
  $timestamp = $images['timestamp'];
  $floor = $images['floor'];
  $dt = DateTime::createFromFormat('Ymd_His', $timestamp);

  $captures[] = [
    'group_key' => $groupKey,
    'floor' => $floor,
    'timestamp' => $timestamp,
    'datetime' => $dt ? $dt->format('Y년 m월 d일 A g:i:s') : $timestamp,
    'time_only' => $dt ? $dt->format('A g:i분') : $timestamp,
    'date_only' => $dt ? $dt->format('Y-m-d') : '',
    'yolo' => isset($images['YOLO']) ? buildDetectionImageUrl($images['YOLO']) : null,
    'slam' => isset($images['SLAM']) ? buildDetectionImageUrl($images['SLAM']) : null,
  ];
}

usort($captures, function (array $left, array $right) use ($sort, $order): int {
  if ($sort === 'floor') {
    preg_match('/\d+/', $left['floor'], $leftFloorMatch);
    preg_match('/\d+/', $right['floor'], $rightFloorMatch);
    $leftFloor = isset($leftFloorMatch[0]) ? (int)$leftFloorMatch[0] : 0;
    $rightFloor = isset($rightFloorMatch[0]) ? (int)$rightFloorMatch[0] : 0;
    $comparison = $leftFloor <=> $rightFloor;

    if ($comparison === 0) {
      $comparison = strcmp($left['timestamp'], $right['timestamp']);
    }
  } else {
    $comparison = strcmp($left['timestamp'], $right['timestamp']);

    if ($comparison === 0) {
      $comparison = strcmp($left['floor'], $right['floor']);
    }
  }

  return $order === 'asc' ? $comparison : -$comparison;
});
?>

<style>
.monitor-shell {
  padding: 20px;
}

.monitor-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 18px;
}

.monitor-meta {
  color: var(--muted);
  font-size: 14px;
}

.monitor-toolbar {
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

.capture-wall {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
  gap: 16px;
}

.capture-pair {
  padding: 14px;
  border-radius: 22px;
  border: 1px solid rgba(142, 185, 200, 0.7);
  background: linear-gradient(180deg, #ffffff, #f7fbfd);
  box-shadow: 0 18px 34px rgba(8, 56, 74, 0.06);
}

.capture-headline {
  padding: 12px 14px;
  border-radius: 16px;
  background: #dcecf3;
  color: var(--surface-strong-2);
  font-size: 15px;
  font-weight: 800;
  text-align: center;
}

.capture-subline {
  margin-top: 8px;
  margin-bottom: 12px;
  color: var(--muted);
  font-size: 13px;
  text-align: center;
}

.capture-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
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
  padding: 54px 24px;
  border-radius: 22px;
  text-align: center;
  background: linear-gradient(180deg, #ffffff, #f5fafc);
  border: 1px dashed rgba(131, 176, 191, 0.9);
}

.empty-state h3 {
  margin: 0;
  color: var(--surface-strong);
  font-size: 22px;
}

.empty-state p {
  margin: 10px 0 0;
  color: var(--muted);
}

@media (max-width: 860px) {
  .monitor-head {
    flex-direction: column;
    align-items: stretch;
  }

  .capture-wall {
    grid-template-columns: 1fr;
  }

  .capture-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<section class="section-card monitor-shell">
  <div class="monitor-head">
    <div>
      <h2 class="section-title">통합 모니터링</h2>
      <p class="section-subtitle">최근 감지 이미지 전체 목록</p>
    </div>
    <div class="monitor-toolbar">
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
      <div class="monitor-meta">감지 쌍 수: <?= count($captures) ?></div>
    </div>
  </div>

  <?php if (count($captures) === 0): ?>
    <div class="empty-state">
      <h3>표시할 감지 이미지가 없습니다.</h3>
      <p>YOLO 이미지와 SLAM 맵이 함께 생성되면 여기에 표시됩니다.</p>
    </div>
  <?php else: ?>
    <div class="capture-wall">
      <?php foreach ($captures as $capture): ?>
        <article class="capture-pair">
          <div class="capture-headline"><?= htmlspecialchars($capture['floor']) ?> · <?= htmlspecialchars($capture['time_only']) ?></div>
          <div class="capture-subline"><?= htmlspecialchars($capture['date_only']) ?> · <?= htmlspecialchars($capture['datetime']) ?></div>

          <div class="capture-grid">
            <section class="capture-card">
              <div class="capture-label">YOLO 이미지</div>
              <div class="capture-frame">
                <?php if ($capture['yolo']): ?>
                  <img src="<?= htmlspecialchars($capture['yolo']) ?>" alt="YOLO 감지 이미지">
                <?php else: ?>
                  <div class="capture-empty">YOLO 이미지가 없습니다.</div>
                <?php endif; ?>
              </div>
            </section>

            <section class="capture-card">
              <div class="capture-label">SLAM 맵</div>
              <div class="capture-frame">
                <?php if ($capture['slam']): ?>
                  <img src="<?= htmlspecialchars($capture['slam']) ?>" alt="SLAM 맵 이미지">
                <?php else: ?>
                  <div class="capture-empty">SLAM 이미지가 없습니다.</div>
                <?php endif; ?>
              </div>
            </section>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

</div>
</body>
</html>
