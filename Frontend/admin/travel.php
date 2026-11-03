<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('admin');
$rows = q("SELECT c.id,c.name,c.dob,c.passport_no,u.name parent_name,u.phone,
  (SELECT COUNT(*) FROM schedule s JOIN vaccines v ON v.id=s.vaccine_id WHERE s.child_id=c.id AND v.travel_required=1) req,
  (SELECT COUNT(*) FROM schedule s JOIN vaccines v ON v.id=s.vaccine_id WHERE s.child_id=c.id AND v.travel_required=1 AND s.status='vaccinated') done,
  (SELECT CONCAT(destination,'|',travel_date) FROM travel_plans WHERE member_id=c.id AND travel_date>=CURDATE() ORDER BY travel_date LIMIT 1) trip
  FROM children c JOIN users u ON u.id=c.parent_id WHERE c.member_type='adult' ORDER BY c.name")->fetchAll();
$cleared = count(array_filter($rows, fn($r) => $r['done'] >= $r['req']));
$soon = count(array_filter($rows, fn($r) => $r['trip'] && (strtotime(explode('|', $r['trip'])[1]) - time()) / 86400 <= 30 && $r['done'] < $r['req']));
page_start('Travel clearance', 'admin'); ?>
<div class="strip"><div><b data-count="<?= count($rows) ?>"><?= count($rows) ?></b><span><i class="bi bi-person-badge"></i>Adults</span></div>
  <div><b data-count="<?= $cleared ?>"><?= $cleared ?></b><span><i class="bi bi-patch-check"></i>Cleared to travel</span></div>
  <div><b data-count="<?= count($rows) - $cleared ?>"><?= count($rows) - $cleared ?></b><span><i class="bi bi-slash-circle"></i>Not cleared</span></div>
  <div><b data-count="<?= $soon ?>"><?= $soon ?></b><span><i class="bi bi-exclamation-triangle"></i>Flying within 30 days, not cleared</span></div></div>
<div class="card"><div class="tools"><div class="f" style="min-width:260px"><label for="s">Search</label><input id="s" data-filter="#tt" placeholder="Adult or account holder"></div></div>
<?php if (!$rows): empty_state('No adults registered yet.'); else: ?><div class="tbl"><table id="tt"><thead><tr><th>Adult</th><th>Account holder</th><th>Required vaccines</th><th>Next trip</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): $p = $r['req'] ? round($r['done'] / $r['req'] * 100) : 100; $ok = $r['done'] >= $r['req']; $trip = $r['trip'] ? explode('|', $r['trip']) : null; ?>
<tr><td><b><?= e($r['name']) ?></b><br><small><?= e($r['passport_no'] ?: 'No passport on file') ?></small></td><td><?= e($r['parent_name']) ?><br><small><?= e($r['phone']) ?></small></td>
  <td style="min-width:150px"><div class="bar"><i style="width:<?= $p ?>%"></i></div><small><?= (int)$r['done'] ?> of <?= (int)$r['req'] ?> done</small></td>
  <td><?= $trip ? e($trip[0]) . '<br><small>' . fmt_date($trip[1]) . '</small>' : '—' ?></td><td><?= $ok ? badge('cleared') : badge('not_cleared') ?></td>
  <td><a class="btn btn-g btn-sm" href="children.php?id=<?= (int)$r['id'] ?>">View</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
<?php page_end();
