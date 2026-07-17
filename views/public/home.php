<?php
/** @var array $stats @var array $latest */
?>
<section id="hero" style="position:relative;overflow:hidden;background:linear-gradient(160deg, var(--primary) 0%, color-mix(in srgb, var(--primary) 70%, #1c1030) 100%);color:#fff;padding:66px 24px 80px">
  <canvas id="starfield" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;opacity:.65" aria-hidden="true"></canvas>
  <div style="position:relative;z-index:1;max-width:840px;margin:0 auto;text-align:center">
    <div style="display:inline-block;font-size:12.5px;font-weight:500;letter-spacing:.04em;background:rgba(255,255,255,.14);padding:6px 14px;border-radius:999px;margin-bottom:20px">คลังปัญญา · เผยแพร่ · ต่อยอด</div>
    <h1 style="font-size:40px;line-height:1.2;font-weight:700;margin:0 0 14px;text-wrap:balance">ระบบคลังงานวิจัยและโครงงาน</h1>
    <p style="font-size:17px;opacity:.9;margin:0 auto 30px;max-width:600px;text-wrap:pretty">รวบรวมงานวิจัยของครู โครงงานนักเรียนนักศึกษา สิ่งประดิษฐ์ และโครงงานวิทยาศาสตร์ ของวิทยาลัยอาชีวศึกษาร้อยเอ็ด</p>
    <form method="get" action="<?= h(url('search')) ?>" style="display:flex;background:var(--surface);border-radius:14px;padding:7px;box-shadow:var(--shadow-lg);max-width:620px;margin:0 auto;gap:6px">
      <input name="q" placeholder="ค้นหาชื่อเรื่อง ผู้จัดทำ หรือคำสำคัญ…" style="flex:1;border:none;background:transparent;color:var(--text);font-size:15px;padding:12px 14px;outline:none"/>
      <button type="submit" style="background:var(--primary);color:var(--primary-fg);border:none;border-radius:9px;padding:0 24px;font-weight:600;font-size:14.5px;cursor:pointer">ค้นหา</button>
    </form>
    <div style="margin-top:16px;font-size:13px;opacity:.82;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
      <span>ยอดนิยม:</span>
      <a class="lnk" href="<?= h(url('search?q=' . rawurlencode('ผ้าไหม'))) ?>" style="color:#fff;opacity:.95">ผ้าไหม</a>·
      <a class="lnk" href="<?= h(url('search?q=' . rawurlencode('พลังงานแสงอาทิตย์'))) ?>" style="color:#fff;opacity:.95">พลังงานแสงอาทิตย์</a>·
      <a class="lnk" href="<?= h(url('search?q=' . rawurlencode('บัญชี'))) ?>" style="color:#fff;opacity:.95">บัญชี</a>
    </div>
  </div>
</section>
<script>
(function () {
  var canvas = document.getElementById('starfield');
  var hero   = document.getElementById('hero');
  if (!canvas) return;
  var ctx = canvas.getContext('2d');

  var W, H, pts, mouse = { x: -9999, y: -9999 };

  /* ── config ─────────────────────────────── */
  var N_PTS      = 90;   // number of particles
  var MAX_DIST   = 140;  // max line distance
  var MOUSE_R    = 180;  // mouse influence radius
  var MOUSE_PULL = 0.04; // how strongly mouse attracts
  var SPEED      = 0.4;

  function resize() {
    W = canvas.width  = hero.offsetWidth;
    H = canvas.height = hero.offsetHeight;
  }

  function randBetween(a, b) { return a + Math.random() * (b - a); }

  function initPts() {
    pts = [];
    for (var i = 0; i < N_PTS; i++) {
      pts.push({
        x: randBetween(0, W),
        y: randBetween(0, H),
        vx: randBetween(-SPEED, SPEED),
        vy: randBetween(-SPEED, SPEED),
        r: randBetween(1.2, 2.8),
        alpha: randBetween(0.4, 1),
      });
    }
  }

  function draw() {
    ctx.clearRect(0, 0, W, H);

    /* move */
    pts.forEach(function (p) {
      /* gentle mouse pull */
      var dx = mouse.x - p.x, dy = mouse.y - p.y;
      var dist = Math.sqrt(dx * dx + dy * dy);
      if (dist < MOUSE_R && dist > 0) {
        var f = (1 - dist / MOUSE_R) * MOUSE_PULL;
        p.vx += dx / dist * f;
        p.vy += dy / dist * f;
      }

      /* speed cap */
      var spd = Math.sqrt(p.vx * p.vx + p.vy * p.vy);
      if (spd > SPEED * 2.5) { p.vx *= 0.92; p.vy *= 0.92; }

      p.x += p.vx;  p.y += p.vy;

      /* wrap */
      if (p.x < -20) p.x = W + 20;
      if (p.x > W + 20) p.x = -20;
      if (p.y < -20) p.y = H + 20;
      if (p.y > H + 20) p.y = -20;
    });

    /* lines */
    for (var i = 0; i < pts.length; i++) {
      for (var j = i + 1; j < pts.length; j++) {
        var dx = pts[i].x - pts[j].x;
        var dy = pts[i].y - pts[j].y;
        var d  = Math.sqrt(dx * dx + dy * dy);
        if (d < MAX_DIST) {
          ctx.beginPath();
          ctx.moveTo(pts[i].x, pts[i].y);
          ctx.lineTo(pts[j].x, pts[j].y);
          ctx.strokeStyle = 'rgba(255,255,255,' + ((1 - d / MAX_DIST) * 0.25) + ')';
          ctx.lineWidth = 0.8;
          ctx.stroke();
        }
      }
    }

    /* dots */
    pts.forEach(function (p) {
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(255,255,255,' + p.alpha + ')';
      ctx.fill();
    });

    requestAnimationFrame(draw);
  }

  /* track mouse relative to hero */
  document.addEventListener('mousemove', function (e) {
    var rect = hero.getBoundingClientRect();
    mouse.x = e.clientX - rect.left;
    mouse.y = e.clientY - rect.top;
  });
  hero.addEventListener('mouseleave', function () {
    mouse.x = -9999; mouse.y = -9999;
  });

  /* touch support */
  hero.addEventListener('touchmove', function (e) {
    var rect = hero.getBoundingClientRect();
    var t = e.touches[0];
    mouse.x = t.clientX - rect.left;
    mouse.y = t.clientY - rect.top;
  }, { passive: true });

  window.addEventListener('resize', function () { resize(); initPts(); });
  resize(); initPts(); draw();
})();
</script>

<div style="width:100%;max-width:1600px;margin:0 auto;padding:0 clamp(24px,3vw,56px)">
  <!-- stats -->
  <section style="margin-top:-42px;position:relative;z-index:2;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px 22px;box-shadow:var(--shadow)">
      <div style="font-size:13px;color:var(--muted);font-weight:500">งานวิจัยทั้งหมด</div>
      <div style="font-size:34px;font-weight:700;margin-top:4px"><?= (int) $stats['total'] ?></div>
      <div style="font-size:12px;color:var(--muted)">เผยแพร่แล้ว</div>
    </div>
    <?php foreach ($stats['cats'] as $c): ?>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px 22px;box-shadow:var(--shadow)">
        <div style="display:flex;align-items:center;gap:8px"><span style="width:10px;height:10px;border-radius:3px;background:<?= h($c['color']) ?>"></span><div style="font-size:12.5px;color:var(--muted);font-weight:500;line-height:1.3"><?= h($c['name']) ?></div></div>
        <div style="font-size:30px;font-weight:700;margin-top:6px"><?= (int) $c['count'] ?></div>
      </div>
    <?php endforeach; ?>
  </section>

  <!-- latest -->
  <section style="margin:52px 0 70px">
    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:20px">
      <h2 style="font-size:23px;font-weight:700;margin:0">งานวิจัยล่าสุด</h2>
      <a class="lnk" href="<?= h(url('search')) ?>" style="font-weight:600;font-size:14px">ดูทั้งหมด →</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:18px">
      <?php foreach ($latest as $r): ?>
        <a href="<?= h(url('research/' . $r['id'])) ?>" class="card-hover card-lift" style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;box-shadow:var(--shadow);text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:10px">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
            <span style="font-size:11.5px;font-weight:600;color:<?= h($r['color']) ?>;background:color-mix(in srgb, <?= h($r['color']) ?> 13%, transparent);padding:4px 10px;border-radius:999px"><?= h($r['catName']) ?></span>
            <span style="font-size:12px;color:var(--faint)"><?= (int) $r['pub_year'] ?></span>
          </div>
          <div style="font-weight:600;font-size:15.5px;line-height:1.4;color:var(--text)"><?= h($r['title_th']) ?></div>
          <div style="font-size:12.5px;color:var(--muted);line-height:1.5;margin-top:-2px"><?= h($r['abstractShort']) ?></div>
          <div style="margin-top:auto;padding-top:10px;border-top:1px solid var(--border-2);display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted)">
            <span style="width:24px;height:24px;border-radius:50%;background:var(--primary-soft);color:var(--primary-text);display:grid;place-items:center;font-weight:600;font-size:11px"><?= h($r['authorInitial']) ?></span>
            <span><?= h($r['leadAuthor']) ?> · <?= h($r['dept']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</div>
