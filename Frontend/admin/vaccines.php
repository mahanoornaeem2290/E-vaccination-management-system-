<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post('action'); $id = (int)post('id');
    if ($act === 'save') {
        $name = post('name'); $desc = post('description'); $age = (int)post('age_days');
        $aud = post('audience') === 'adult' ? 'adult' : 'child'; $trv = isset($_POST['travel_required']) && $aud === 'adult' ? 1 : 0;
        if ($aud === 'adult') $age = 0; // adults are due as soon as they register
        if ($name === '' || $age < 0) { flash('Enter a vaccine name and a valid age in days.', 'bad'); }
        elseif ($id) { $old = q('SELECT audience FROM vaccines WHERE id=?', [$id])->fetchColumn(); $aud = $old ?: $aud; $trv = $aud === 'adult' ? $trv : 0;
            q('UPDATE vaccines SET name=?,description=?,age_days=?,travel_required=? WHERE id=?', [$name, $desc, $aud === 'adult' ? 0 : $age, $trv, $id]);
            if ($aud === 'child') q('UPDATE schedule s JOIN children c ON c.id=s.child_id SET s.due_date=DATE_ADD(c.dob,INTERVAL ? DAY) WHERE s.vaccine_id=? AND s.status=\'pending\'', [$age, $id]);
            flash('Vaccine updated.'); }
        else { q('INSERT INTO vaccines(name,description,age_days,audience,travel_required) VALUES(?,?,?,?,?)', [$name, $desc, $age, $aud, $trv]);
            $vid = (int)db()->lastInsertId();
            q("INSERT INTO schedule(child_id,vaccine_id,due_date) SELECT id,?,IF(member_type='adult',CURDATE(),DATE_ADD(dob,INTERVAL ? DAY)) FROM children WHERE member_type=?", [$vid, $age, $aud]);
            $hs = q('SELECT id FROM hospitals')->fetchAll(PDO::FETCH_COLUMN); foreach ($hs as $hid) q('INSERT IGNORE INTO hospital_vaccines(hospital_id,vaccine_id,stock) VALUES(?,?,0)', [$hid, $vid]);
            flash('Vaccine added and scheduled for all registered ' . ($aud === 'adult' ? 'adults.' : 'children.')); }
    } elseif ($act === 'travel') {
        q("UPDATE vaccines SET travel_required=1-travel_required WHERE id=? AND audience='adult'", [$id]); flash('Travel requirement changed.');
    } elseif ($act === 'toggle') {
        q('UPDATE vaccines SET available=1-available WHERE id=?', [$id]); flash('Availability changed.');
    } elseif ($act === 'delete') {
        try { q('DELETE FROM vaccines WHERE id=?', [$id]); flash('Vaccine deleted.'); }
        catch (PDOException $x) { flash('This vaccine is already in children’s schedules. Mark it unavailable instead.', 'bad'); }
    }
    redirect('admin/vaccines.php');
}
$edit = isset($_GET['edit']) ? q('SELECT * FROM vaccines WHERE id=?', [(int)$_GET['edit']])->fetch() : null;
$list = q("SELECT v.*,(SELECT COALESCE(SUM(hv.stock),0) FROM hospital_vaccines hv JOIN hospitals h ON h.id=hv.hospital_id WHERE hv.vaccine_id=v.id AND h.status='approved') stock FROM vaccines v ORDER BY audience,age_days,id")->fetchAll();
page_start('Vaccines', 'admin'); ?>
<div class="card"><h2><?= $edit ? 'Edit vaccine' : 'Add a vaccine' ?></h2>
  <form method="post" class="fgrid"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <?php field('Vaccine name', 'name', 'text', ['value' => $edit['name'] ?? '']);
    if (!$edit) field('Who is it for?', 'audience', 'select', ['value' => 'child', 'options' => ['child' => 'Children (due by age)', 'adult' => 'Adults (due on registration)']]);
    if (($edit['audience'] ?? 'child') === 'child') field('Due at age (days from birth)', 'age_days', 'number', ['value' => $edit['age_days'] ?? 0, 'attr' => 'min="0" max="4000"']);
    field('Description', 'description', 'text', ['value' => $edit['description'] ?? '', 'required' => false, 'class' => 'wide']); ?>
    <?php if (!$edit || $edit['audience'] === 'adult'): ?><label class="wide"><input type="checkbox" name="travel_required" value="1" <?= ($edit['travel_required'] ?? 0) ? 'checked' : '' ?>> Required for travelling abroad (adults only). Adults cannot be cleared to travel until it is given.</label><?php endif; ?>
    <div class="wide actions"><button class="btn btn-p"><?= $edit ? 'Save changes' : 'Add vaccine' ?></button><?php if ($edit): ?><a class="btn btn-g" href="vaccines.php">Cancel</a><?php endif; ?></div></form></div>
<div class="card"><h2>Vaccine list</h2><div class="tbl"><table>
  <tr><th>Vaccine</th><th>For</th><th>Due at</th><th>Total stock</th><th>Availability</th><th></th></tr>
  <?php foreach ($list as $v): $ok = $v['available'] && $v['stock'] > 0; ?>
  <tr><td><b><?= e($v['name']) ?></b><br><small><?= e($v['description']) ?></small></td>
    <td><?= type_badge($v['audience']) ?><?= $v['travel_required'] ? '<br><span class="badge warn"><i class="bi bi-airplane"></i> Travel</span>' : '' ?></td><td><?= $v['audience'] === 'adult' ? 'On registration' : ($v['age_days'] == 0 ? 'Birth' : round($v['age_days'] / 7) . ' weeks') ?></td><td><?= (int)$v['stock'] ?></td>
    <td><?= badge($ok ? 'available' : 'unavailable') ?><?= !$v['available'] ? '<br><small>Switched off by admin</small>' : '' ?></td>
    <td><div class="actions">
      <a class="btn btn-g btn-sm" href="?edit=<?= $v['id'] ?>">Edit</a>
      <?php if ($v['audience'] === 'adult'): ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $v['id'] ?>"><button class="btn btn-g btn-sm" name="action" value="travel"><?= $v['travel_required'] ? 'Not required to travel' : 'Require for travel' ?></button></form><?php endif; ?>
      <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $v['id'] ?>"><button class="btn btn-g btn-sm" name="action" value="toggle"><?= $v['available'] ? 'Make unavailable' : 'Make available' ?></button></form>
      <form method="post" class="inline" data-confirm="Delete this vaccine?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $v['id'] ?>"><button class="btn btn-d btn-sm" name="action" value="delete">Delete</button></form>
    </div></td></tr><?php endforeach; ?></table></div></div>
<?php page_end();
