<?php
/** @var array $counts @var array $catBars @var array $yearBars @var array $pending */

/* ── Donut chart geometry ───────────────────────────────────── */
$total  = max(1, (int) $counts['total']);
$r = 54; $cx = 64; $cy = 64; $stroke = 13;
$circumference = 2 * M_PI * $r;
$donutSegs = []; $offset = 0;
foreach ($catBars as $b) {
    $frac = $b['count'] / $total;
    $donutSegs[] = ['color' => $b['color'], 'dash' => $frac * $circumference, 'gap' => $circumference, 'offset' => $offset];
    $offset += $frac * $circumference + 1.6; // 1.6px gap between segments
}

/* ── Bar chart ──────────────────────────────────────────────── */
$maxY   = max(1, ...array_map(fn($y) => $y['count'], $yearBars) ?: [1]);
$barW   = 36; $barGap = 14; $chartH = 130; $labelH = 22;
$svgW   = max(200, count($yearBars) * ($barW + $barGap) - $barGap + 20);
?>
<style>
@keyframes fade-up { from { opacity:0; transform:translateY(10px) } to { opacity:1; transform:translateY(0) } }
@keyframes grow-h  { from { height:0 } to { height:var(--h) } }
.db-card { animation: fade-up .28s both }
.db-card:nth-child(1){animation-delay:.00s}
.db-card:nth-child(2){animation-delay:.05s}
.db-card:nth-child(3){animation-delay:.10s}
.db-card:nth-child(4){animation-delay:.15s}
</style>

<!-- ① Stat cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
<?php
$stats = [
    ['งานวิจัยทั้งหมด', $counts['total'],     '#6c47ff', 'var(--primary-soft)', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ['เผยแพร่แล้ว',     $counts['published'], '#22c55e', '#dcfce7',             'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0'],
    ['รอตรวจสอบ',       $counts['pending'],   '#f59e0b', '#fef3c7',             'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0'],
    ['ผู้ใช้งาน',       $counts['users'],     '#38bdf8', '#e0f2fe',             'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
];
foreach ($stats as $i => [$label, $value, $color, $bg, $path]): ?>
  <div class="db-card" style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px 22px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:14px;position:relative;overflow:hidden">
    <div style="position:absolute;left:0;top:0;bottom:0;width:4px;background:<?= $color ?>;border-radius:16px 0 0 16px"></div>
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <div style="font-size:13px;color:var(--muted);font-weight:500"><?= h($label) ?></div>
      <span style="width:36px;height:36px;border-radius:10px;background:<?= $bg ?>;display:grid;place-items:center;flex:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="<?= $color ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><path d="<?= $path ?>"/></svg>
      </span>
    </div>
    <div style="font-size:36px;font-weight:800;letter-spacing:-.5px;color:var(--text);line-height:1"><?= number_format((int) $value) ?></div>
    <?php if ($i === 0): ?>
      <div style="height:5px;background:var(--surface-3);border-radius:99px;overflow:hidden">
        <div style="height:100%;width:<?= $total > 0 ? round($counts['published']/$total*100) : 0 ?>%;background:<?= $color ?>;border-radius:99px;transition:width .6s .3s"></div>
      </div>
      <div style="font-size:11.5px;color:var(--muted)">เผยแพร่ <?= $total > 0 ? round($counts['published']/$total*100) : 0 ?>%</div>
    <?php else: ?>
      <div style="font-size:11.5px;color:var(--muted)">&nbsp;</div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<!-- ② Charts row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">

  <!-- Donut chart -->
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:22px;box-shadow:var(--shadow)">
    <div style="font-weight:700;font-size:15px;margin-bottom:18px">สัดส่วนตามประเภทงานวิจัย</div>
    <div style="display:flex;align-items:center;gap:24px">
      <div style="flex:none;position:relative;width:128px;height:128px">
        <svg viewBox="0 0 128 128" width="128" height="128">
          <!-- track -->
          <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none" stroke="var(--surface-3)" stroke-width="<?= $stroke ?>"/>
          <?php foreach ($donutSegs as $seg): ?>
          <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none"
            stroke="<?= h($seg['color']) ?>" stroke-width="<?= $stroke ?>"
            stroke-dasharray="<?= round($seg['dash'],2) ?> <?= round($seg['gap'],2) ?>"
            stroke-dashoffset="<?= round($circumference/4 - $seg['offset'],2) ?>"
            stroke-linecap="round"
            style="transition:stroke-dasharray .6s"/>
          <?php endforeach; ?>
          <text x="<?= $cx ?>" y="<?= $cy - 5 ?>" text-anchor="middle" dominant-baseline="middle" style="fill:var(--text);font-size:20px;font-weight:800"><?= $total ?></text>
          <text x="<?= $cx ?>" y="<?= $cy + 14 ?>" text-anchor="middle" dominant-baseline="middle" style="fill:var(--muted);font-size:9px">รายการ</text>
        </svg>
      </div>
      <div style="flex:1;display:flex;flex-direction:column;gap:9px;min-width:0">
        <?php foreach ($catBars as $b): ?>
          <div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
              <span style="color:var(--text);display:flex;align-items:center;gap:6px">
                <span style="width:8px;height:8px;border-radius:50%;background:<?= h($b['color']) ?>;flex:none"></span>
                <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100px"><?= h($b['name']) ?></span>
              </span>
              <span style="color:var(--muted);font-weight:600;flex:none"><?= (int) $b['count'] ?></span>
            </div>
            <div style="height:5px;background:var(--surface-3);border-radius:99px;overflow:hidden">
              <div style="height:100%;width:<?= h($b['pct']) ?>;background:<?= h($b['color']) ?>;border-radius:99px"></div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (!$catBars): ?>
          <div style="color:var(--muted);font-size:13px">ยังไม่มีข้อมูล</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Bar chart (SVG) -->
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:22px;box-shadow:var(--shadow)">
    <div style="font-weight:700;font-size:15px;margin-bottom:18px">จำนวนตามปีที่เผยแพร่</div>
    <?php if ($yearBars): ?>
    <div style="overflow-x:auto">
      <svg viewBox="0 0 <?= $svgW ?> <?= $chartH + $labelH + 6 ?>" width="<?= $svgW ?>" height="<?= $chartH + $labelH + 6 ?>" style="display:block;min-width:100%">
        <?php foreach ($yearBars as $i => $y):
          $bh  = round($y['count'] / $maxY * $chartH);
          $bx  = 10 + $i * ($barW + $barGap);
          $by  = $chartH - $bh;
          $rx  = 6; // corner radius
        ?>
        <!-- bar group -->
        <g>
          <!-- background track -->
          <rect x="<?= $bx ?>" y="0" width="<?= $barW ?>" height="<?= $chartH ?>" rx="<?= $rx ?>" fill="var(--surface-3)"/>
          <!-- filled bar -->
          <rect x="<?= $bx ?>" y="<?= $by ?>" width="<?= $barW ?>" height="<?= $bh ?>" rx="<?= $rx ?>"
            fill="url(#barGrad<?= $i ?>)">
            <animate attributeName="height" from="0" to="<?= $bh ?>" dur=".5s" begin="<?= $i*0.08 ?>s" fill="freeze" calcMode="spline" keySplines="0.16 1 0.3 1"/>
            <animate attributeName="y" from="<?= $chartH ?>" to="<?= $by ?>" dur=".5s" begin="<?= $i*0.08 ?>s" fill="freeze" calcMode="spline" keySplines="0.16 1 0.3 1"/>
          </rect>
          <defs>
            <linearGradient id="barGrad<?= $i ?>" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#6c47ff"/>
              <stop offset="100%" stop-color="#9f7fff" stop-opacity=".7"/>
            </linearGradient>
          </defs>
          <!-- count label -->
          <text x="<?= $bx + $barW/2 ?>" y="<?= max($by - 6, 12) ?>" text-anchor="middle" style="fill:var(--muted);font-size:10px;font-weight:600"><?= (int) $y['count'] ?></text>
          <!-- year label -->
          <text x="<?= $bx + $barW/2 ?>" y="<?= $chartH + $labelH ?>" text-anchor="middle" style="fill:var(--muted);font-size:10.5px"><?= (int) $y['year'] ?></text>
        </g>
        <?php endforeach; ?>
      </svg>
    </div>
    <?php else: ?>
      <div style="height:120px;display:grid;place-items:center;color:var(--muted);font-size:13px">ยังไม่มีข้อมูล</div>
    <?php endif; ?>
  </div>
</div>

<!-- ③ Pending queue -->
<div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);overflow:hidden">
  <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:9px">
      รายการรอตรวจสอบ
      <?php if ($pending): ?>
        <span style="font-size:11.5px;font-weight:700;color:#fff;background:#f59e0b;padding:3px 9px;border-radius:999px;min-width:20px;text-align:center"><?= count($pending) ?></span>
      <?php endif; ?>
    </div>
    <a href="<?= h(url('admin/research?status=รอตรวจสอบ')) ?>" style="font-size:13px;font-weight:600;color:var(--primary-text);text-decoration:none">ดูทั้งหมด →</a>
  </div>
  <?php if (!$pending): ?>
    <div style="padding:36px;text-align:center">
      <div style="width:48px;height:48px;background:var(--ok-soft);border-radius:50%;display:grid;place-items:center;margin:0 auto 12px">
        <svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px"><path d="M5 13l4 4L19 7"/></svg>
      </div>
      <div style="color:var(--muted);font-size:13.5px">ไม่มีรายการรอตรวจสอบ</div>
    </div>
  <?php else: ?>
    <?php foreach ($pending as $r): ?>
      <div style="display:flex;align-items:center;gap:14px;padding:14px 22px;border-bottom:1px solid var(--border-2);transition:background .15s" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
        <div style="width:40px;height:40px;border-radius:10px;background:color-mix(in srgb,<?= h($r['color']) ?> 14%,transparent);color:<?= h($r['color']) ?>;display:grid;place-items:center;font-weight:700;font-size:13px;flex:none"><?= h($r['pubYearShort'] ?? mb_substr((string)($r['pub_year'] ?? ''), -2)) ?></div>
        <div style="flex:1;min-width:0">
          <div style="font-size:14px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($r['title_th']) ?></div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px"><?= h($r['catName']) ?> · <?= h($r['leadAuthor']) ?> · <?= h($r['dept']) ?></div>
        </div>
        <div style="display:flex;gap:8px;flex:none">
          <a href="<?= h(url('research/' . $r['id'])) ?>" style="height:34px;padding:0 13px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-weight:600;font-size:12.5px;cursor:pointer;text-decoration:none;display:grid;place-items:center">ดู</a>
          <form method="post" action="<?= h(url('admin/research/' . $r['id'] . '/approve')) ?>" style="margin:0">
            <?= csrf_field() ?>
            <button type="submit" style="height:34px;padding:0 15px;background:var(--ok);color:#fff;border:none;border-radius:8px;font-weight:600;font-size:12.5px;cursor:pointer">เผยแพร่</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
