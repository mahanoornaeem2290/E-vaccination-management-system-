<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
$u = require_role('parent');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post('action'); $id = (int)post('id');
    if ($act === 'delete') {
        q('DELETE FROM children WHERE id=? AND parent_id=?', [$id, $u['id']]); flash('Person removed.'); redirect('parent/children.php');
    }
    $name = post('name'); $dob = post('dob'); $g = post('gender'); $bg = post('blood_group');
    $type = post('member_type') === 'adult' ? 'adult' : 'child'; $passport = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', post('passport_no')));
    if ($id) $type = (string)q('SELECT member_type FROM children WHERE id=? AND parent_id=?', [$id, $u['id']])->fetchColumn() ?: $type; // type cannot change after creation
    $d = DateTime::createFromFormat('Y-m-d', $dob);
    if (mb_strlen($name) < 2) $errors[] = 'Enter the person’s name.';
    if (!$d || $d->format('Y-m-d') !== $dob || $d > new DateTime('today')) $errors[] = 'Enter a valid date of birth that is not in the future.';
    elseif ($type === 'child' && $d < new DateTime('-18 years')) $errors[] = 'Choose Adult for anyone aged 18 or older.';
    elseif ($type === 'adult' && $d > new DateTime('-18 years')) $errors[] = 'Choose Child for anyone under 18.';
    elseif ($type === 'adult' && $d < new DateTime('-110 years')) $errors[] = 'Enter a valid date of birth.';
    if (strlen($passport) > 20) $errors[] = 'Passport number is too long.';
    if (!in_array($g, ['Male', 'Female'], true)) $errors[] = 'Choose a gender.';
    if (!$errors) {
        if ($id) {
            $old = q('SELECT dob FROM children WHERE id=? AND parent_id=?', [$id, $u['id']])->fetchColumn();
            if ($old) {
                q('UPDATE children SET name=?,dob=?,gender=?,blood_group=?,passport_no=? WHERE id=?', [$name, $dob, $g, $bg, $type === 'adult' ? $passport : '', $id]);
                if ($old !== $dob && $type === 'child') q("UPDATE schedule s JOIN vaccines v ON v.id=s.vaccine_id SET s.due_date=DATE_ADD(?,INTERVAL v.age_days DAY) WHERE s.child_id=? AND s.status='pending'", [$dob, $id]);
                flash('Details updated.');
            }
        } else {
            q('INSERT INTO children(parent_id,name,dob,gender,blood_group,member_type,passport_no) VALUES(?,?,?,?,?,?,?)', [$u['id'], $name, $dob, $g, $bg, $type, $type === 'adult' ? $passport : '']);
            make_schedule((int)db()->lastInsertId(), $dob, $type);
            flash('Details saved. Your vaccination schedule is ready. Choose a vaccine and book a hospital appointment.');
            redirect('parent/book.php');
        }
        redirect('parent/children.php');
    }
}
if (isset($_GET['id']) && ($c = q('SELECT * FROM children WHERE id=? AND parent_id=?', [(int)$_GET['id'], $u['id']])->fetch())) {
    $rows = q('SELECT s.*,v.name vaccine,v.description FROM schedule s JOIN vaccines v ON v.id=s.vaccine_id WHERE s.child_id=? ORDER BY s.due_date,v.id', [$c['id']])->fetchAll();
    page_start($c['name'] . '’s schedule', 'parent'); ?>
    <p><a href="children.php">← My family</a></p>
    <?php if ($c['member_type'] === 'adult'): $ts = travel_status((int)$c['id']); ?><div class="alert <?= $ts['cleared'] ? 'ok' : 'bad' ?>"><i class="bi bi-airplane"></i> <?= $ts['cleared'] ? 'Cleared to travel abroad.' : 'Not cleared to travel abroad yet: ' . e(implode(', ', $ts['missing'])) . '.' ?> <a href="travel.php?id=<?= (int)$c['id'] ?>">Open travel clearance</a></div><?php endif; ?>
    <div class="card"><div class="tbl"><table><tr><th>Vaccine</th><th>Due date</th><th>Status</th><th></th></tr>
    <?php foreach ($rows as $r): ?><tr><td><b><?= e($r['vaccine']) ?></b><br><small><?= e($r['description']) ?></small></td><td><?= fmt_date($r['due_date']) ?></td><td><?= due_badge($r['status'], $r['due_date']) ?></td>
      <td><?= $r['status'] === 'pending' ? '<a class="btn btn-p btn-sm" href="book.php?schedule=' . $r['id'] . '">Book</a>' : ($r['status'] === 'vaccinated' ? '<small>Given ' . fmt_date($r['given_on']) . '</small>' : '') ?></td></tr><?php endforeach; ?></table></div></div>
    <?php page_end(); exit;
}
$edit = isset($_GET['edit']) ? q('SELECT * FROM children WHERE id=? AND parent_id=?', [(int)$_GET['edit'], $u['id']])->fetch() : null;
$kids = q('SELECT * FROM children WHERE parent_id=? ORDER BY name', [$u['id']])->fetchAll();
$v = fn($k) => ['value' => $_POST[$k] ?? ($edit[$k] ?? '')];
page_start('My family', 'parent'); ?>
<div class="card"><h2><?= $edit ? 'Edit ' . e($edit['name']) : 'Add a child or an adult' ?></h2>
  <?php foreach ($errors as $er): ?><div class="alert bad"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="fgrid"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <?php if (!$edit) field('Who is this for?', 'member_type', 'select', ['value' => $_POST['member_type'] ?? 'child', 'options' => ['child' => 'Under 18', 'adult' => '18 or older']]);
    field('Full name', 'name', 'text', $v('name')); field('Date of birth', 'dob', 'date', $v('dob') + ['attr' => 'max="' . date('Y-m-d') . '"']);
    field('Gender', 'gender', 'select', $v('gender') + ['options' => ['' => 'Choose', 'Male' => 'Male', 'Female' => 'Female']]);
    field('Blood group', 'blood_group', 'select', $v('blood_group') + ['required' => false, 'options' => ['' => 'Not sure', 'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-']]); ?>
    <div class="f" id="passport-wrap" <?= ($edit['member_type'] ?? 'child') === 'adult' ? '' : 'hidden' ?>><label for="passport_no">Passport number (optional)</label><input id="passport_no" name="passport_no" maxlength="20" value="<?= e($_POST['passport_no'] ?? ($edit['passport_no'] ?? '')) ?>" autocomplete="off"></div>
    <div class="wide actions"><button class="btn btn-p"><?= $edit ? 'Save changes' : 'Save and book a vaccine' ?></button><?php if ($edit): ?><a class="btn btn-g" href="children.php">Cancel</a><?php endif; ?></div></form></div>
<div class="card"><h2>Registered family</h2><?php if (!$kids): empty_state('No one added yet.'); else: ?><div class="tbl"><table>
  <tr><th>Name</th><th>Type</th><th>Date of birth</th><th>Age</th><th>Gender</th><th>Blood group</th><th></th></tr>
  <?php foreach ($kids as $k): ?><tr><td><b><?= e($k['name']) ?></b></td><td><?= type_badge($k['member_type']) ?></td><td><?= fmt_date($k['dob']) ?></td><td><?= age_label($k['dob']) ?></td><td><?= e($k['gender']) ?></td><td><?= e($k['blood_group'] ?: '—') ?></td>
    <td><div class="actions"><a class="btn btn-p btn-sm" href="?id=<?= $k['id'] ?>">Schedule</a><?php if ($k['member_type'] === 'adult'): ?><a class="btn btn-s btn-sm" href="travel.php?id=<?= $k['id'] ?>"><i class="bi bi-airplane"></i>Travel</a><?php endif; ?><a class="btn btn-g btn-sm" href="?edit=<?= $k['id'] ?>">Edit</a>
    <form method="post" class="inline" data-confirm="Remove this person and their vaccination records?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $k['id'] ?>"><button class="btn btn-d btn-sm" name="action" value="delete">Remove</button></form></div></td></tr>
  <?php endforeach; ?></table></div><?php endif; ?></div>
<?php page_end();
