<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('hospital');
$h = my_hospital();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = q(APPT_SQL . "WHERE a.id=? AND a.hospital_id=? AND a.admin_status='approved' AND a.vax_status='pending'", [(int)post('id'), $h['id']])->fetch();
    if ($a) {
        $pid = (int)q('SELECT parent_id FROM children WHERE id=?', [$a['child_id']])->fetchColumn();
        $pdo = db(); $pdo->beginTransaction();
        if (post('result') === 'vaccinated') {
            q("UPDATE appointments SET vax_status='vaccinated' WHERE id=?", [$a['id']]);
            q("UPDATE schedule SET status='vaccinated',given_on=CURDATE() WHERE id=?", [$a['schedule_id']]);
            q('UPDATE hospital_vaccines SET stock=GREATEST(stock-1,0) WHERE hospital_id=? AND vaccine_id=?', [$h['id'], $a['vaccine_id']]);
            notify($pid, "{$a['child']} received {$a['vaccine']} at {$h['name']}. The record is updated.");
            flash('Marked as vaccinated.');
        } else {
            q("UPDATE appointments SET vax_status='not_vaccinated' WHERE id=?", [$a['id']]);
            q("UPDATE schedule SET status='pending' WHERE id=?", [$a['schedule_id']]);
            notify($pid, "{$a['child']} was not vaccinated ({$a['vaccine']}) at {$h['name']}. Please book a new appointment.");
            flash('Marked as not vaccinated. The parent can rebook.');
        }
        $pdo->commit();
    }
    redirect('hospital/appointments.php?tab=' . (post('tab') ?: 'todo'));
}
$tab = ($_GET['tab'] ?? 'todo') === 'done' ? 'done' : 'todo';
$cond = $tab === 'todo' ? "a.admin_status='approved' AND a.vax_status='pending'" : "a.vax_status<>'pending'";
$rows = q(APPT_SQL . "WHERE a.hospital_id=? AND $cond ORDER BY a.appt_date " . ($tab === 'todo' ? 'ASC' : 'DESC') . ',a.slot', [$h['id']])->fetchAll();
page_start('Appointments', 'hospital'); ?>
<div class="tabs" style="max-width:360px"><a class="<?= $tab === 'todo' ? 'on' : '' ?>" href="?tab=todo">To vaccinate</a><a class="<?= $tab === 'done' ? 'on' : '' ?>" href="?tab=done">Completed</a></div>
<div class="card"><?php if (!$rows): empty_state($tab === 'todo' ? 'No approved appointments waiting.' : 'No completed appointments yet.'); else: ?><div class="tbl"><table>
  <tr><th>Date &amp; time</th><th>Person</th><th>Account holder</th><th>Vaccine</th><th><?= $tab === 'todo' ? 'Update status' : 'Result' ?></th></tr>
  <?php foreach ($rows as $r): ?><tr><td><?= fmt_date($r['appt_date']) ?><br><small><?= e($r['slot']) ?></small></td><td><?= e($r['child']) ?></td><td><?= e($r['parent']) ?></td><td><?= e($r['vaccine']) ?></td>
    <td><?php if ($tab === 'todo'): ?><form method="post" class="actions"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="tab" value="todo">
      <button class="btn btn-p btn-sm" name="result" value="vaccinated">Vaccinated</button><button class="btn btn-g btn-sm" name="result" value="not_vaccinated">Not vaccinated</button></form>
    <?php else: echo badge($r['vax_status']); endif; ?></td></tr><?php endforeach; ?></table></div><?php endif; ?></div>
<?php page_end();
