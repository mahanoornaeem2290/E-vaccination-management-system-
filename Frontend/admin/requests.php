<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $d = post('decision') === 'approve' ? 'approved' : 'rejected';
    $a = q("SELECT a.*,c.parent_id,c.name child,v.name vaccine,h.user_id hu FROM appointments a JOIN children c ON c.id=a.child_id
        JOIN vaccines v ON v.id=a.vaccine_id JOIN hospitals h ON h.id=a.hospital_id WHERE a.id=? AND a.admin_status='pending'", [(int)post('id')])->fetch();
    if ($a) {
        q('UPDATE appointments SET admin_status=?,admin_note=? WHERE id=?', [$d, post('note'), $a['id']]);
        if ($d === 'rejected') q("UPDATE schedule SET status='pending' WHERE id=?", [$a['schedule_id']]);
        $when = fmt_date($a['appt_date']) . ', ' . $a['slot'];
        notify((int)$a['parent_id'], "Your request for {$a['child']} ({$a['vaccine']}) on $when was $d." . (post('note') ? ' Note: ' . post('note') : ''));
        if ($d === 'approved') notify((int)$a['hu'], "New appointment: {$a['child']} · {$a['vaccine']} on $when.");
        flash("Request $d.");
    }
    redirect('admin/requests.php');
}
$pend = q(APPT_SQL . "WHERE a.admin_status='pending' ORDER BY a.appt_date,a.slot")->fetchAll();
$done = q(APPT_SQL . "WHERE a.admin_status<>'pending' ORDER BY a.id DESC LIMIT 10")->fetchAll();
page_start('Requests from parents', 'admin'); ?>
<div class="card"><h2>Waiting for approval</h2>
<?php if (!$pend): empty_state('All caught up. No pending requests.'); else: ?><div class="tbl"><table>
  <tr><th>Person &amp; account holder</th><th>Vaccine</th><th>Hospital</th><th>Date &amp; time</th><th>Decision</th></tr>
  <?php foreach ($pend as $r): ?><tr><td><b><?= e($r['child']) ?></b><br><small><?= e($r['parent']) ?></small></td><td><?= e($r['vaccine']) ?></td><td><?= e($r['hospital']) ?></td>
    <td><?= fmt_date($r['appt_date']) ?><br><small><?= e($r['slot']) ?></small></td>
    <td><form method="post" class="actions"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
      <input name="note" placeholder="Note (optional)" style="width:150px" maxlength="200">
      <button class="btn btn-p btn-sm" name="decision" value="approve">Approve</button><button class="btn btn-d btn-sm" name="decision" value="reject">Reject</button></form></td></tr>
  <?php endforeach; ?></table></div><?php endif; ?></div>
<div class="card"><h2>Recently decided</h2><?php if (!$done): empty_state('Nothing yet.'); else: ?><div class="tbl"><table>
  <tr><th>Person</th><th>Vaccine</th><th>Hospital</th><th>Date</th><th>Decision</th></tr>
  <?php foreach ($done as $r): ?><tr><td><?= e($r['child']) ?></td><td><?= e($r['vaccine']) ?></td><td><?= e($r['hospital']) ?></td><td><?= fmt_date($r['appt_date']) ?></td><td><?= badge($r['admin_status']) ?></td></tr><?php endforeach; ?></table></div><?php endif; ?></div>
<?php page_end();
