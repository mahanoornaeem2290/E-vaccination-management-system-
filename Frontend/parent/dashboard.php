<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
$u = require_role('parent');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); q('UPDATE notifications SET is_read=1 WHERE user_id=?', [$u['id']]); redirect('parent/dashboard.php'); }
$kids = q("SELECT c.*,(SELECT COUNT(*) FROM schedule WHERE child_id=c.id AND status='vaccinated') done,(SELECT COUNT(*) FROM schedule WHERE child_id=c.id) total FROM children c WHERE parent_id=? ORDER BY c.name", [$u['id']])->fetchAll();
$due = q("SELECT s.*,c.name child,v.name vaccine FROM schedule s JOIN children c ON c.id=s.child_id JOIN vaccines v ON v.id=s.vaccine_id WHERE c.parent_id=? AND s.status IN ('pending','booked') ORDER BY s.due_date LIMIT 8", [$u['id']])->fetchAll();
$notes = q('SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 6', [$u['id']])->fetchAll();
$overdue = (int)q("SELECT COUNT(*) FROM schedule s JOIN children c ON c.id=s.child_id WHERE c.parent_id=? AND s.status='pending' AND s.due_date<CURDATE()", [$u['id']])->fetchColumn();
page_start('Hello, ' . explode(' ', $u['name'])[0], 'parent');
if ($overdue): ?><div class="alert bad"><?= $overdue ?> vaccine<?= $overdue > 1 ? 's are' : ' is' ?> overdue. <a href="book.php">Book an appointment now</a>.</div><?php endif; ?>
<?php live_pill(); ?>
<div class="strip">
  <div><b data-kpi="members" data-count="<?= count($kids) ?>"><?= count($kids) ?></b><span><i class="bi bi-people"></i>Family members</span></div>
  <div><b data-kpi="given" data-count="<?= array_sum(array_column($kids, 'done')) ?>"><?= array_sum(array_column($kids, 'done')) ?></b><span><i class="bi bi-shield-check"></i>Doses given</span></div>
  <div><b data-kpi="booked" data-count="0">0</b><span><i class="bi bi-calendar-check"></i>Booked</span></div>
  <div><b data-kpi="due" data-count="0">0</b><span><i class="bi bi-hourglass-split"></i>Still to book</span></div>
</div>
<?php if ($kids): ?>
<section class="card progress-card" aria-labelledby="progressTitle">
  <div class="progress-head">
    <div><h2 id="progressTitle">Vaccination progress</h2><p class="muted">Compare doses given, booked, and still pending for each family member.</p></div>
    <div class="chart-tools" role="group" aria-label="Vaccination graph controls">
      <button class="btn btn-g btn-sm" id="chartOrientation" type="button" aria-pressed="false"><i class="bi bi-bar-chart-steps"></i><span>Vertical view</span></button>
      <button class="btn btn-g btn-sm" id="chartReset" type="button"><i class="bi bi-arrow-counterclockwise"></i>Reset</button>
      <button class="btn btn-g btn-sm" id="chartDownload" type="button"><i class="bi bi-download"></i>Save image</button>
    </div>
  </div>
  <div class="chart progress-chart"><canvas id="cMembers" role="img" aria-label="Stacked bar chart comparing vaccination status for family members"></canvas></div>
  <p class="chart-help"><i class="bi bi-info-circle"></i> Select a status in the legend to show or hide it. Hover a bar for exact dose counts.</p>
</section>
<?php endif; ?>
<div class="card"><h2>My family</h2>
<?php if (!$kids): empty_state('You have not added anyone yet.'); ?><a class="btn btn-p" href="children.php">Add a family member</a>
<?php else: ?><div class="kids"><?php foreach ($kids as $k): $p = $k['total'] ? round($k['done'] / $k['total'] * 100) : 0; ?>
  <div class="kid <?= e($k['member_type']) ?>"><h3><?= e($k['name']) ?> <?= type_badge($k['member_type']) ?></h3><small><?= e($k['gender']) ?> · <?= age_label($k['dob']) ?></small>
    <div class="bar"><i style="width:<?= $p ?>%"></i></div><small><?= $k['done'] ?> of <?= $k['total'] ?> doses given</small>
    <div class="actions" style="margin-top:.7rem"><a class="btn btn-g btn-sm" href="children.php?id=<?= $k['id'] ?>">Schedule</a><a class="btn btn-p btn-sm" href="report.php?id=<?= $k['id'] ?>">Report</a><?php if ($k['member_type'] === 'adult'): ?><a class="btn btn-s btn-sm" href="travel.php?id=<?= $k['id'] ?>"><i class="bi bi-airplane"></i>Travel</a><?php endif; ?></div></div>
<?php endforeach; ?></div><?php endif; ?></div>
<div class="cols">
  <div class="card"><h2>Upcoming vaccinations</h2>
    <?php if (!$due): empty_state('Nothing due. Everyone is up to date.'); else: ?><div class="tbl"><table><tr><th>Person</th><th>Vaccine</th><th>Due</th><th>Status</th></tr>
    <?php foreach ($due as $d): ?><tr><td><?= e($d['child']) ?></td><td><?= e($d['vaccine']) ?></td><td><?= fmt_date($d['due_date']) ?></td><td><?= due_badge($d['status'], $d['due_date']) ?></td></tr><?php endforeach; ?></table></div>
    <a class="btn btn-s btn-sm" href="book.php">Book a vaccine</a><?php endif; ?></div>
  <div class="card"><h2>Notifications</h2>
    <?php if (!$notes): empty_state('No notifications yet.'); else: foreach ($notes as $n): ?>
      <p style="<?= $n['is_read'] ? 'color:var(--muted)' : 'font-weight:600' ?>"><i class="bi bi-bell"></i> <?= e($n['message']) ?><br><small><?= date('d M, H:i', strtotime($n['created_at'])) ?></small></p>
    <?php endforeach; ?><form method="post"><?= csrf_field() ?><button class="btn btn-g btn-sm">Mark all as read</button></form><?php endif; ?></div>
</div>
<?php page_end($kids ? live_scripts('parent') : '');
