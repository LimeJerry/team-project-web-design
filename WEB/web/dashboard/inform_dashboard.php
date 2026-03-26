<?php include 'header.php'; ?>

<?php
$recentDetections = [
  ["time" => "14:42:12", "label" => "14:42 감지", "img" => "images/test1.jpg"],
  ["time" => "14:38:50", "label" => "14:38 감지", "img" => "images/test2.jpg"],
  ["time" => "14:31:08", "label" => "14:31 감지", "img" => "images/test3.jpg"],
];

$systemLogs = [
  ["time" => "14:10", "msg" => "순찰 시작"],
  ["time" => "14:29", "msg" => "장애물 감지"],
  ["time" => "14:34", "msg" => "경로 재탐색 수행"],
];
?>

<style>
.info-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
  gap: 18px;
}

.info-card {
  padding: 20px;
}

.info-card-head {
  margin-bottom: 16px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
  gap: 14px;
}

.detect-item {
  border-radius: 20px;
  overflow: hidden;
  background: linear-gradient(180deg, #ffffff, #f6fbfd);
  border: 1px solid rgba(142, 185, 200, 0.7);
}

.detect-thumb {
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
  display: block;
  background: #dcecf3;
}

.detect-body {
  padding: 14px;
}

.detect-label {
  font-size: 16px;
  font-weight: 800;
  color: var(--surface-strong);
}

.detect-time {
  margin-top: 8px;
  color: var(--muted);
  font-size: 14px;
}

.log-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.log-item {
  padding: 14px 16px;
  border-radius: 18px;
  background: linear-gradient(180deg, #ffffff, #f7fbfd);
  border: 1px solid rgba(142, 185, 200, 0.7);
}

.log-time {
  font-size: 13px;
  color: var(--muted);
}

.log-message {
  margin-top: 6px;
  font-size: 15px;
  font-weight: 800;
  color: var(--text);
}

@media (max-width: 980px) {
  .info-layout {
    grid-template-columns: 1fr;
  }
}
</style>

<div class="info-layout">
  <section class="section-card info-card">
    <div class="info-card-head">
      <h2 class="section-title">최근 감지 정보</h2>
      <p class="section-subtitle">최신 감지 시각</p>
    </div>

    <div class="info-grid">
      <?php foreach ($recentDetections as $detect): ?>
        <article class="detect-item">
          <img class="detect-thumb" src="<?= htmlspecialchars($detect['img']) ?>" alt="감지 이미지">
          <div class="detect-body">
            <div class="detect-label"><?= htmlspecialchars($detect['label']) ?></div>
            <div class="detect-time"><?= htmlspecialchars($detect['time']) ?></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <aside class="section-card info-card">
    <div class="info-card-head">
      <h2 class="section-title">시스템 로그</h2>
      <p class="section-subtitle">운행 기록</p>
    </div>

    <div class="log-list">
      <?php foreach ($systemLogs as $log): ?>
        <div class="log-item">
          <div class="log-time"><?= htmlspecialchars($log['time']) ?></div>
          <div class="log-message"><?= htmlspecialchars($log['msg']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </aside>
</div>

</div>
</body>
</html>
