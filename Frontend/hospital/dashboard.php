<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('hospital');
$h = my_hospital();
$n = fn(string $w) => (int)q("SELECT COUNT(*) FROM appointments WHERE hospital_id=? AND $w", [$h['id']])->fetchColumn();
$today = q(APPT_SQL . "WHERE a.hospital_id=? AND a.admin_status='approved' AND a.vax_status='pending' AND a.appt_date>=CURDATE() ORDER BY a.appt_date,a.slot LIMIT 8", [$h['id']])->fetchAll();
$low = q('SELECT v.name,hv.stock FROM hospital_vaccines hv JOIN vaccines v ON v.id=hv.vaccine_id WHERE hv.hospital_id=? AND hv.stock<10 ORDER BY hv.stock', [$h['id']])->fetchAll();
page_start($h['name'], 'hospital');
live_pill(); ?>
<div class="strip">
  <div><b data-kpi="today" data-count="<?= $n("appt_date=CURDATE() AND admin_status='approved'") ?>"><?= $n("appt_date=CURDATE() AND admin_status='approved'") ?></b><span><i class="bi bi-calendar-day"></i>Today</span></div>
  <div><b data-kpi="upcoming" data-count="<?= $n("admin_status='approved' AND vax_status='pending' AND appt_date>=CURDATE()") ?>"><?= $n("admin_status='approved' AND vax_status='pending' AND appt_date>=CURDATE()") ?></b><span><i class="bi bi-hourglass-split"></i>Upcoming</span></div>
  <div><b data-kpi="overdue" data-count="<?= $n("admin_status='approved' AND vax_status='pending' AND appt_date<CURDATE()") ?>"><?= $n("admin_status='approved' AND vax_status='pending' AND appt_date<CURDATE()") ?></b><span><i class="bi bi-exclamation-circle"></i>Need a status update</span></div>
  <div><b data-kpi="given" data-count="<?= $n("vax_status='vaccinated'") ?>"><?= $n("vax_status='vaccinated'") ?></b><span><i class="bi bi-shield-check"></i>Vaccinated</span></div>
</div>
<div class="cols">
  <div class="card"><h2>Vaccine stock (live)</h2><div class="chart tall"><canvas id="cStock"></canvas></div></div>
  <div class="card"><h2>Appointments, next 7 days</h2><div class="chart tall"><canvas id="cWeek"></canvas></div></div>
</div>
<div class="cols">
  <div class="card"><h2>Upcoming appointments</h2><?php if (!$today): empty_state('No approved appointments yet.'); else: ?><div class="tbl"><table>
    <tr><th>Date &amp; time</th><th>Person</th><th>Vaccine</th></tr>
    <?php foreach ($today as $r): ?><tr><td><?= fmt_date($r['appt_date']) ?><br><small><?= e($r['slot']) ?></small></td><td><?= e($r['child']) ?></td><td><?= e($r['vaccine']) ?></td></tr><?php endforeach; ?></table></div>
    <a class="btn btn-p btn-sm" href="appointments.php">Update statuses</a><?php endif; ?></div>
  <div class="card"><h2>Low stock</h2><?php if (!$low): empty_state('Stock levels look healthy.'); else: ?><div class="tbl"><table><tr><th>Vaccine</th><th>In stock</th></tr>
    <?php foreach ($low as $l): ?><tr><td><?= e($l['name']) ?></td><td><?= badge($l['stock'] > 0 ? 'pending' : 'unavailable') ?> <?= (int)$l['stock'] ?></td></tr><?php endforeach; ?></table></div>
    <a class="btn btn-g btn-sm" href="stock.php">Manage stock</a><?php endif; ?></div>
</div>
<?php page_end(live_scripts('hospital'));
