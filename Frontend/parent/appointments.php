<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
$u = require_role('parent');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = q(APPT_SQL . "WHERE a.id=? AND c.parent_id=? AND a.vax_status='pending'", [(int)post('id'), $u['id']])->fetch();
    if ($a) { q('DELETE FROM appointments WHERE id=?', [$a['id']]); q("UPDATE schedule SET status='pending' WHERE id=?", [$a['schedule_id']]); flash('Appointment cancelled. You can book it again.'); }
    redirect('parent/appointments.php');
}
$rows = q(APPT_SQL . 'WHERE c.parent_id=? ORDER BY a.appt_date DESC,a.id DESC', [$u['id']])->fetchAll();
page_start('My appointments', 'parent'); ?>
<div class="card"><?php if (!$rows): empty_state('No appointments yet.'); echo '<a class="btn btn-p" href="book.php">Book a vaccine</a>'; else: ?><div class="tbl"><table>
  <tr><th>Date &amp; time</th><th>Person</th><th>Vaccine</th><th>Hospital</th><th>Admin</th><th>Vaccination</th><th></th></tr>
  <?php foreach ($rows as $r): ?><tr><td><?= fmt_date($r['appt_date']) ?><br><small><?= e($r['slot']) ?></small></td><td><?= e($r['child']) ?></td><td><?= e($r['vaccine']) ?></td><td><?= e($r['hospital']) ?></td>
    <td><?= badge($r['admin_status']) ?><?= $r['admin_note'] ? '<br><small>' . e($r['admin_note']) . '</small>' : '' ?></td><td><?= badge($r['vax_status']) ?></td>
    <td><?php if ($r['vax_status'] === 'pending' && $r['admin_status'] !== 'rejected'): ?><form method="post" data-confirm="Cancel this appointment?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-g btn-sm">Cancel</button></form><?php endif; ?></td></tr>
  <?php endforeach; ?></table></div><?php endif; ?></div>
<?php page_end();
