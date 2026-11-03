<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('admin');
$st = $_GET['status'] ?? ''; $from = $_GET['from'] ?? ''; $to = $_GET['to'] ?? '';
$w = []; $args = [];
if (in_array($st, ['pending', 'approved', 'rejected'], true)) { $w[] = 'a.admin_status=?'; $args[] = $st; }
if ($from !== '') { $w[] = 'a.appt_date>=?'; $args[] = $from; }
if ($to !== '') { $w[] = 'a.appt_date<=?'; $args[] = $to; }
$rows = q(APPT_SQL . ($w ? 'WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY a.appt_date DESC,a.slot', $args)->fetchAll();
if (isset($_GET['export'])) {
    header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="bookings.csv"');
    $o = fopen('php://output', 'w'); fputcsv($o, ['Date', 'Time', 'Child', 'Parent', 'Vaccine', 'Hospital', 'Approval', 'Vaccination']);
    foreach ($rows as $r) fputcsv($o, [$r['appt_date'], $r['slot'], $r['child'], $r['parent'], $r['vaccine'], $r['hospital'], $r['admin_status'], $r['vax_status']]);
    exit;
}
page_start('Booking details', 'admin'); ?>
<div class="card"><form class="tools" method="get">
  <?php field('Approval', 'status', 'select', ['value' => $st, 'required' => false, 'options' => ['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']]);
  field('From', 'from', 'date', ['value' => $from, 'required' => false]); field('To', 'to', 'date', ['value' => $to, 'required' => false]); ?>
  <button class="btn btn-p">Filter</button><a class="btn btn-g" href="bookings.php">Reset</a>
  <a class="btn btn-g" href="?<?= e(http_build_query(array_filter(['status' => $st, 'from' => $from, 'to' => $to]) + ['export' => 1])) ?>"><i class="bi bi-download"></i>Export CSV</a></form>
<?php if (!$rows): empty_state('No bookings match these filters.'); else: ?><div class="tbl"><table>
  <tr><th>Date &amp; time</th><th>Person</th><th>Account holder</th><th>Vaccine</th><th>Hospital</th><th>Approval</th><th>Vaccination</th></tr>
  <?php foreach ($rows as $r): ?><tr><td><?= fmt_date($r['appt_date']) ?><br><small><?= e($r['slot']) ?></small></td><td><?= e($r['child']) ?></td><td><?= e($r['parent']) ?></td><td><?= e($r['vaccine']) ?></td><td><?= e($r['hospital']) ?></td><td><?= badge($r['admin_status']) ?></td><td><?= badge($r['vax_status']) ?></td></tr><?php endforeach; ?></table></div><?php endif; ?></div>
<?php page_end();
