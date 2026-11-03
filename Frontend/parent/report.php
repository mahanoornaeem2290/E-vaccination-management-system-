<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
$u = require_role('parent');
$kids = q('SELECT * FROM children WHERE parent_id=? ORDER BY name', [$u['id']])->fetchAll();
$cid = (int)($_GET['id'] ?? ($kids[0]['id'] ?? 0));
$c = null; foreach ($kids as $k) if ((int)$k['id'] === $cid) $c = $k;
page_start('Vaccination report', 'parent');
if (!$c) { echo '<div class="card">'; empty_state('Add a child to see their vaccination report.'); echo '</div>'; page_end(); exit; }
$rows = q("SELECT s.*,v.name vaccine,
  (SELECT h.name FROM appointments a JOIN hospitals h ON h.id=a.hospital_id WHERE a.schedule_id=s.id AND a.vax_status='vaccinated' ORDER BY a.id DESC LIMIT 1) hospital
  FROM schedule s JOIN vaccines v ON v.id=s.vaccine_id WHERE s.child_id=? ORDER BY s.due_date,v.id", [$c['id']])->fetchAll();
$done = count(array_filter($rows, fn($r) => $r['status'] === 'vaccinated'));
?>
<form class="tools noprint" method="get"><?php field('Child', 'id', 'select', ['value' => $c['id'], 'options' => array_column($kids, 'name', 'id')]); ?>
  <button class="btn btn-g">Show</button><button type="button" class="btn btn-p" onclick="print()"><i class="bi bi-printer"></i>Print / Save as PDF</button></form>
<div class="cert">
  <h2><?= APP_NAME ?> vaccination record</h2><p class="sub">Generated on <?= date('d M Y') ?></p>
  <p><b><?= e($c['name']) ?></b> · <?= e($c['gender']) ?> · <?= $c['member_type'] === 'adult' ? 'Adult' : 'Child' ?> · Born <?= fmt_date($c['dob']) ?> · Blood group <?= e($c['blood_group'] ?: '—') ?><br><?= $c['member_type'] === 'adult' ? 'Account holder' : 'Parent' ?>: <?= e($u['name']) ?> · <?= $done ?> of <?= count($rows) ?> doses completed</p>
  <div class="tbl"><table><tr><th>Vaccine</th><th>Due date</th><th>Status</th><th>Given on</th><th>Hospital</th></tr>
  <?php foreach ($rows as $r): ?><tr><td><?= e($r['vaccine']) ?></td><td><?= fmt_date($r['due_date']) ?></td><td><?= due_badge($r['status'], $r['due_date']) ?></td><td><?= fmt_date($r['given_on']) ?></td><td><?= e($r['hospital'] ?? '—') ?></td></tr><?php endforeach; ?></table></div>
</div>
<?php page_end();
