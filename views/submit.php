<?php
/** @var ?array $editing @var array $enabledCats @var array $depts @var array $chapters
 *  @var string $area  'admin' | 'my'    @var array $statuses */
$area = $area ?? 'admin';
$statuses = $statuses ?? App::STATUSES;
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:10px 12px;font-size:14px;background:var(--surface-2);color:var(--text);outline:none';
$labelStyle = 'display:block;font-size:12.5px;font-weight:500;color:var(--muted);margin-bottom:6px';
$iconBtnDanger = 'width:32px;height:32px;border-radius:8px;border:1px solid var(--danger-soft);background:var(--danger-soft);color:var(--danger);cursor:pointer;font-size:13px';

$e = $editing;
$val = fn (string $k, $d = '') => $e ? ($e[$k] ?? $d) : $d;
$authors = $e['authors'] ?? [['name' => '', 'role' => 'ผู้วิจัยหลัก']];
$keywords = $e['keywords'] ?? [];
$files = $e['files'] ?? [];
$fileByIdx = [];
foreach ($files as $f) { $fileByIdx[(int) $f['chapter_index']] = $f; }
$action = $e ? url($area . '/research/' . $e['id'] . '/edit') : url($area . '/submit');
$cancelUrl = $area === 'admin' ? url('admin/research') : url('my');
$uploaded = count(array_filter($files, fn ($f) => $f['uploaded']));
$note = $area === 'admin'
    ? 'กรอกข้อมูลงานวิจัยและอัปโหลดเอกสารแยกเป็นบท (ไฟล์ PDF) เลือกได้ว่าบทใดเปิดเผยสู่สาธารณะ'
    : 'กรอกข้อมูลงานวิจัยของคุณและอัปโหลดเอกสารแยกเป็นบท เลือก “รอตรวจสอบ” เพื่อส่งให้ผู้ดูแลอนุมัติเผยแพร่';
?>
<div style="animation:fade .25s;max-width:960px">
  <p style="margin:0 0 18px;color:var(--muted);font-size:13.5px"><?= h($note) ?></p>
  <form method="post" action="<?= h($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
      <!-- left: fields -->
      <div style="display:flex;flex-direction:column;gap:18px">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow)">
          <div style="font-weight:600;font-size:15px;margin-bottom:16px">ข้อมูลงานวิจัย</div>
          <label style="<?= $labelStyle ?>">ชื่องานวิจัย (ภาษาไทย) *</label>
          <input name="title_th" value="<?= h($val('title_th')) ?>" required placeholder="เช่น การพัฒนาระบบ…" style="<?= $inputStyle ?>;margin-bottom:14px"/>
          <label style="<?= $labelStyle ?>">ชื่องานวิจัย (ภาษาอังกฤษ)</label>
          <input name="title_en" value="<?= h($val('title_en')) ?>" placeholder="e.g. Development of…" style="<?= $inputStyle ?>;margin-bottom:14px"/>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div><label style="<?= $labelStyle ?>">ประเภทงานวิจัย *</label>
              <select name="category_id" required style="<?= $inputStyle ?>">
                <option value="">— เลือกประเภท —</option>
                <?php foreach ($enabledCats as $c): ?>
                  <option value="<?= (int) $c['id'] ?>" <?= (string) $val('category_id') === (string) $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div><label style="<?= $labelStyle ?>">สาขาวิชา/แผนกวิชา</label>
              <select name="dept" style="<?= $inputStyle ?>">
                <option value="">— เลือก —</option>
                <?php foreach ($depts as $d): ?>
                  <option value="<?= h($d) ?>" <?= $val('dept') === $d ? 'selected' : '' ?>><?= h($d) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px">
            <div><label style="<?= $labelStyle ?>">ปีการศึกษา</label><input name="academic_year" value="<?= h((string) ($val('academic_year') ?: '')) ?>" placeholder="2568" style="<?= $inputStyle ?>"/></div>
            <div><label style="<?= $labelStyle ?>">ปีที่เผยแพร่</label><input name="pub_year" value="<?= h((string) ($val('pub_year') ?: '')) ?>" placeholder="2568" style="<?= $inputStyle ?>"/></div>
          </div>
        </div>

        <!-- authors -->
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow)">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px"><div style="font-weight:600;font-size:15px">ผู้จัดทำ</div><button type="button" data-add-author style="background:var(--primary-soft);color:var(--primary-text);border:none;border-radius:8px;padding:7px 13px;font-weight:600;font-size:12.5px;cursor:pointer">+ เพิ่มผู้จัดทำ</button></div>
          <div data-authors style="display:flex;flex-direction:column;gap:10px">
            <?php foreach ($authors as $a): ?>
              <div data-author-row style="display:flex;gap:10px;align-items:center">
                <input name="author_name[]" data-author-name value="<?= h($a['name']) ?>" placeholder="ชื่อ-สกุล" style="<?= $inputStyle ?>;flex:1"/>
                <select name="author_role[]" style="<?= $inputStyle ?>;width:160px">
                  <?php foreach (App::AUTHOR_ROLES as $role): ?>
                    <option <?= ($a['role'] ?? '') === $role ? 'selected' : '' ?>><?= h($role) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="button" data-remove-author style="<?= $iconBtnDanger ?>;flex:none">✕</button>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- abstract + keywords -->
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow)">
          <div style="font-weight:600;font-size:15px;margin-bottom:16px">บทคัดย่อและคำสำคัญ</div>
          <label style="<?= $labelStyle ?>">บทคัดย่อ (ภาษาไทย)</label>
          <textarea name="abstract_th" rows="4" placeholder="สรุปสาระสำคัญของงานวิจัย…" style="<?= $inputStyle ?>;resize:vertical;margin-bottom:14px"><?= h($val('abstract_th')) ?></textarea>
          <label style="<?= $labelStyle ?>">บทคัดย่อ (ภาษาอังกฤษ)</label>
          <textarea name="abstract_en" rows="3" placeholder="Abstract…" style="<?= $inputStyle ?>;resize:vertical;margin-bottom:14px"><?= h($val('abstract_en')) ?></textarea>
          <label style="<?= $labelStyle ?>">คำสำคัญ (พิมพ์แล้วกด Enter เพื่อเพิ่ม)</label>
          <div data-keywords='<?= h(json_encode(array_values($keywords), JSON_UNESCAPED_UNICODE)) ?>' style="display:flex;flex-wrap:wrap;gap:7px;align-items:center;border:1px solid var(--border);border-radius:10px;padding:8px 10px;background:var(--surface-2)">
            <span data-kw-hidden></span>
            <input data-kw-input placeholder="พิมพ์แล้วกด Enter…" style="flex:1;min-width:120px;border:none;background:transparent;color:var(--text);font-size:13.5px;padding:5px;outline:none"/>
          </div>
        </div>
      </div>

      <!-- right: status + files + submit -->
      <div style="display:flex;flex-direction:column;gap:18px;position:sticky;top:80px">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow)">
          <div style="font-weight:600;font-size:15px;margin-bottom:4px">สถานะ</div>
          <select name="status" style="<?= $inputStyle ?>;margin-top:8px">
            <?php foreach ($statuses as $s): ?>
              <option <?= (string) ($val('status', 'แบบร่าง')) === $s ? 'selected' : '' ?>><?= h($s) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($area !== 'admin'): ?>
            <div style="font-size:11px;color:var(--muted);margin-top:8px">“แบบร่าง” บันทึกไว้แก้ไขต่อได้ · “รอตรวจสอบ” ส่งให้ผู้ดูแลอนุมัติเผยแพร่</div>
          <?php endif; ?>
        </div>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow)">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px"><div style="font-weight:600;font-size:15px">เอกสารแยกบท</div><span style="font-size:12px;font-weight:600;color:var(--primary-text)"><?= $uploaded ?>/<?= count($chapters) ?></span></div>
          <div style="font-size:11px;color:var(--muted);margin-bottom:12px">เลือกไฟล์ PDF ของแต่ละบท และติ๊ก “เปิดเผย” เพื่อให้ดาวน์โหลดได้จากหน้าสาธารณะ</div>
          <div style="display:flex;flex-direction:column;gap:8px">
            <?php foreach ($chapters as $idx => $name): ?>
              <?php $cf = $fileByIdx[$idx] ?? null; $has = $cf && $cf['uploaded']; ?>
              <div data-chapter-row style="display:flex;align-items:center;gap:11px;padding:10px 12px;border:1px dashed <?= $has ? 'var(--ok)' : 'var(--border)' ?>;border-radius:10px;background:<?= $has ? 'var(--ok-soft)' : 'var(--surface-2)' ?>">
                <span data-chapter-badge style="width:22px;height:22px;border-radius:6px;flex:none;display:grid;place-items:center;font-size:12px;font-weight:700;background:<?= $has ? 'var(--ok)' : 'var(--surface-3)' ?>;color:<?= $has ? '#fff' : 'var(--faint)' ?>"><?= $has ? '✓' : '+' ?></span>
                <div style="flex:1;min-width:0">
                  <div style="font-size:13px;font-weight:500"><?= h($name) ?></div>
                  <div data-chapter-hint style="font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= $has ? h($cf['original_name'] ?: 'อัปโหลดแล้ว') : 'ยังไม่อัปโหลด' ?></div>
                  <label style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);margin-top:4px;cursor:pointer">
                    <input type="checkbox" name="chapter_public[<?= $idx ?>]" value="1" <?= ($cf['is_public'] ?? ($idx < 2)) ? 'checked' : '' ?>/> เปิดเผยสาธารณะ
                  </label>
                </div>
                <label style="flex:none;cursor:pointer;font-size:11px;color:var(--primary-text);font-weight:600">
                  เลือกไฟล์
                  <input type="file" name="chapter[<?= $idx ?>]" accept="application/pdf" data-chapter-file style="display:none"/>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div style="display:flex;gap:10px">
          <button type="submit" style="flex:1;background:var(--primary);color:#fff;border:none;border-radius:11px;padding:13px;font-weight:600;font-size:14.5px;cursor:pointer;box-shadow:var(--shadow)"><?= $e ? 'บันทึกการแก้ไข' : ($area === 'admin' ? 'บันทึกงานวิจัย' : 'ส่งงานวิจัย') ?></button>
          <a href="<?= h($cancelUrl) ?>" style="background:var(--surface-2);color:var(--text);border:1px solid var(--border);border-radius:11px;padding:13px 18px;font-weight:600;font-size:14px;cursor:pointer;text-decoration:none;display:grid;place-items:center">ยกเลิก</a>
        </div>
      </div>
    </div>
  </form>
</div>
