<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('admin');
if (isset($_GET['id'])) {
    $c = q('SELECT c.*,u.name parent_name,u.phone,u.email FROM children c JOIN users u ON u.id=c.parent_id WHERE c.id=?', [(int)$_GET['id']])->fetch();
    if (!$c) redirect('admin/children.php');
    $rows = q("SELECT s.*,v.name vaccine,
        (SELECT CONCAT(a.appt_date,'|',a.slot,'|',h.name) FROM appointments a JOIN hospitals h ON h.id=a.hospital_id WHERE a.schedule_id=s.id AND a.admin_status<>'rejected' ORDER BY a.id DESC LIMIT 1) appt
        FROM schedule s JOIN vaccines v ON v.id=s.vaccine_id WHERE s.child_id=? ORDER BY s.due_date,v.id", [$c['id']])->fetchAll();
    page_start($c['name'], 'admin'); ?>
    <p><a href="children.php">← Everyone</a></p>
    <div class="card"><b><?= e($c['name']) ?></b> <?= type_badge($c['member_type']) ?> · <?= e($c['gender']) ?> · born <?= fmt_date($c['dob']) ?> (<?= age_label($c['dob']) ?>) · Blood group <?= e($c['blood_group'] ?: '—') ?><br>
      <small>Parent: <?= e($c['parent_name']) ?> · <?= e($c['phone']) ?> · <?= e($c['email']) ?></small></div>
    <div class="card"><h2>Vaccination schedule</h2><div class="tbl"><table>
      <tr><th>Vaccine</th><th>Due date</th><th>Status</th><th>Date &amp; time of vaccination</th><th>Hospital</th></tr>
      <?php foreach ($rows as $r): $a = $r['appt'] ? explode('|', $r['appt']) : null; ?>
      <tr><td><?= e($r['vaccine']) ?></td><td><?= fmt_date($r['due_date']) ?></td><td><?= due_badge($r['status'], $r['due_date']) ?></td>
        <td><?= $r['status'] === 'vaccinated' ? fmt_date($r['given_on']) : ($a ? fmt_date($a[0]) . ', ' . e($a[1]) : '—') ?></td><td><?= $a ? e($a[2]) : '—' ?></td></tr>
      <?php endforeach; ?></table></div></div>
    <?php page_end(); exit;
}
$kids = q("SELECT c.*,u.name parent_name,u.phone,
  (SELECT COUNT(*) FROM schedule WHERE child_id=c.id AND status='vaccinated') done,
  (SELECT COUNT(*) FROM schedule WHERE child_id=c.id) total,
  (SELECT MIN(due_date) FROM schedule WHERE child_id=c.id AND status<>'vaccinated') next_due
  FROM children c JOIN users u ON u.id=c.parent_id ORDER BY c.name")->fetchAll();
page_start('All people', 'admin'); ?>
<div class="card">
  <div class="tools"><div class="f" style="min-width:260px"><label for="s">Search</label><input id="s" data-filter="#kt" placeholder="Name or account holder"></div></div>
  <?php if (!$kids): empty_state('No one registered yet.'); else: ?><div class="tbl"><table id="kt">
    <thead><tr><th>Person</th><th>Age</th><th>Account holder</th><th>Progress</th><th>Next vaccination due</th><th></th></tr></thead><tbody>
    <?php foreach ($kids as $k): $p = $k['total'] ? round($k['done'] / $k['total'] * 100) : 0; ?>
    <tr><td><b><?= e($k['name']) ?></b> <?= type_badge($k['member_type']) ?><br><small><?= e($k['gender']) ?> · <?= e($k['blood_group'] ?: '—') ?></small></td><td><?= age_label($k['dob']) ?></td>
      <td><?= e($k['parent_name']) ?><br><small><?= e($k['phone']) ?></small></td>
      <td style="min-width:150px"><div class="bar"><i style="width:<?= $p ?>%"></i></div><small><?= $k['done'] ?> of <?= $k['total'] ?> doses</small></td>
      <td><?= $k['next_due'] ? fmt_date($k['next_due']) : 'Complete' ?></td><td><a class="btn btn-g btn-sm" href="?id=<?= $k['id'] ?>">View</a></td></tr>
    <?php endforeach; ?></tbody></table></div><?php endif; ?>
</div>
<?php page_end();
