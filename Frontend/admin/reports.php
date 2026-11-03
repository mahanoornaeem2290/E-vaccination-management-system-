<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('admin');
$from = $_GET['from'] ?? date('Y-m-01'); $to = $_GET['to'] ?? date('Y-m-t');
$rows = q(APPT_SQL . "WHERE a.appt_date BETWEEN ? AND ? AND a.admin_status<>'rejected' ORDER BY a.appt_date,a.slot", [$from, $to])->fetchAll();
$byDate = []; $tot = ['vaccinated' => 0, 'pending' => 0, 'not_vaccinated' => 0];
foreach ($rows as $r) { $byDate[$r['appt_date']][] = $r; $tot[$r['vax_status']]++; }
$vacc = q("SELECT v.name,COUNT(*) c FROM schedule s JOIN vaccines v ON v.id=s.vaccine_id WHERE s.status='vaccinated' AND s.given_on BETWEEN ? AND ? GROUP BY v.id,v.name ORDER BY c DESC", [$from, $to])->fetchAll();
page_start('Vaccination report', 'admin'); ?>
<form class="tools noprint" method="get"><?php field('From', 'from', 'date', ['value' => $from]); field('To', 'to', 'date', ['value' => $to]); ?>
  <button class="btn btn-p">Run report</button><button type="button" class="btn btn-g" onclick="print()"><i class="bi bi-printer"></i>Print / Save as PDF</button></form>
<div class="strip"><div><b><?= count($rows) ?></b><span>Appointments</span></div><div><b><?= $tot['vaccinated'] ?></b><span>Vaccinated</span></div>
  <div><b><?= $tot['pending'] ?></b><span>Still pending</span></div><div><b><?= $tot['not_vaccinated'] ?></b><span>Not vaccinated</span></div></div>
<div class="card"><h2>Date-wise report: <?= fmt_date($from) ?> to <?= fmt_date($to) ?></h2>
<?php if (!$byDate): empty_state('No appointments in this period.'); else: ?><div class="tbl"><table>
  <tr><th>Date</th><th>Time</th><th>Person</th><th>Vaccine</th><th>Hospital</th><th>Result</th></tr>
  <?php foreach ($byDate as $d => $list): foreach ($list as $i => $r): ?><tr><td><?= $i === 0 ? '<b>' . fmt_date($d) . '</b>' : '' ?></td><td><?= e($r['slot']) ?></td><td><?= e($r['child']) ?></td><td><?= e($r['vaccine']) ?></td><td><?= e($r['hospital']) ?></td><td><?= badge($r['vax_status']) ?></td></tr><?php endforeach; endforeach; ?></table></div><?php endif; ?></div>
<?php if ($vacc): ?><div class="card"><h2>Doses given by vaccine</h2><div class="tbl"><table><tr><th>Vaccine</th><th>Doses</th></tr>
  <?php foreach ($vacc as $v): ?><tr><td><?= e($v['name']) ?></td><td><?= (int)$v['c'] ?></td></tr><?php endforeach; ?></table></div></div><?php endif; ?>
<?php page_end();
