<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('admin');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post('action'); $id = (int)post('id');
    if ($act === 'save') {
        $name = post('name'); $email = strtolower(post('email')); $phone = post('phone'); $addr = post('address'); $city = post('city'); $pass = (string)($_POST['password'] ?? '');
        $h = $id ? q('SELECT * FROM hospitals WHERE id=?', [$id])->fetch() : null;
        if (mb_strlen($name) < 3) $errors[] = 'Enter the hospital name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
        elseif (q('SELECT 1 FROM users WHERE email=? AND id<>?', [$email, $h['user_id'] ?? 0])->fetch()) $errors[] = 'That email is already in use.';
        if (!valid_phone($phone)) $errors[] = 'Enter a valid phone number.';
        if ($addr === '' || $city === '') $errors[] = 'Address and city are required.';
        if (!$h && !valid_password($pass)) $errors[] = 'Set a password with 8+ characters, letters and numbers.';
        if (!$errors) {
            $pdo = db(); $pdo->beginTransaction();
            if ($h) {
                q('UPDATE users SET name=?,email=?,phone=? WHERE id=?', [$name, $email, $phone, $h['user_id']]);
                q('UPDATE hospitals SET name=?,address=?,city=?,phone=? WHERE id=?', [$name, $addr, $city, $phone, $id]);
            } else {
                q("INSERT INTO users(name,email,phone,password,role) VALUES(?,?,?,?,'hospital')", [$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT)]);
                q("INSERT INTO hospitals(user_id,name,address,city,phone,status) VALUES(?,?,?,?,?,'approved')", [$pdo->lastInsertId(), $name, $addr, $city, $phone]);
            }
            $pdo->commit(); flash($h ? 'Hospital updated.' : 'Hospital added and approved.'); redirect('admin/hospitals.php');
        }
    } elseif ($act === 'status') {
        $st = in_array(post('status'), ['approved', 'rejected'], true) ? post('status') : 'rejected';
        q('UPDATE hospitals SET status=? WHERE id=?', [$st, $id]);
        $uid = (int)q('SELECT user_id FROM hospitals WHERE id=?', [$id])->fetchColumn();
        notify($uid, "Your hospital registration was $st."); flash("Hospital $st."); redirect('admin/hospitals.php');
    } elseif ($act === 'delete') {
        q("UPDATE schedule SET status='pending' WHERE id IN (SELECT schedule_id FROM appointments WHERE hospital_id=? AND vax_status='pending' AND admin_status<>'rejected')", [$id]);
        q('DELETE FROM users WHERE id=(SELECT user_id FROM hospitals WHERE id=?)', [$id]);
        flash('Hospital deleted. Affected children can book again.'); redirect('admin/hospitals.php');
    }
}
$edit = isset($_GET['edit']) ? q('SELECT h.*,u.email FROM hospitals h JOIN users u ON u.id=h.user_id WHERE h.id=?', [(int)$_GET['edit']])->fetch() : null;
$list = q('SELECT h.*,u.email FROM hospitals h JOIN users u ON u.id=h.user_id ORDER BY FIELD(h.status,\'pending\',\'approved\',\'rejected\'),h.name')->fetchAll();
page_start('Hospitals', 'admin'); ?>
<div class="card"><h2><?= $edit ? 'Edit hospital' : 'Add a hospital' ?></h2>
  <?php foreach ($errors as $er): ?><div class="alert bad"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="fgrid"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <?php $v = fn($k) => ['value' => $_POST[$k] ?? ($edit[$k] ?? '')];
    field('Hospital name', 'name', 'text', $v('name')); field('Login email', 'email', 'email', $v('email')); field('Phone', 'phone', 'tel', $v('phone'));
    field('City', 'city', 'text', $v('city')); field('Address', 'address', 'text', $v('address') + ['class' => 'wide']);
    if (!$edit) field('Password for hospital login', 'password', 'password', ['attr' => 'minlength="8"']); ?>
    <div class="wide actions"><button class="btn btn-p"><?= $edit ? 'Save changes' : 'Add hospital' ?></button><?php if ($edit): ?><a class="btn btn-g" href="hospitals.php">Cancel</a><?php endif; ?></div></form></div>
<div class="card"><h2>All hospitals</h2><div class="tbl"><table>
  <tr><th>Hospital</th><th>Contact</th><th>City</th><th>Status</th><th></th></tr>
  <?php foreach ($list as $h): ?><tr><td><b><?= e($h['name']) ?></b><br><small><?= e($h['address']) ?></small></td><td><?= e($h['phone']) ?><br><small><?= e($h['email']) ?></small></td><td><?= e($h['city']) ?></td><td><?= badge($h['status']) ?></td>
    <td><div class="actions">
      <?php if ($h['status'] !== 'approved'): ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $h['id'] ?>"><input type="hidden" name="status" value="approved"><button class="btn btn-p btn-sm" name="action" value="status">Approve</button></form><?php endif; ?>
      <?php if ($h['status'] === 'pending'): ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $h['id'] ?>"><input type="hidden" name="status" value="rejected"><button class="btn btn-g btn-sm" name="action" value="status">Reject</button></form><?php endif; ?>
      <a class="btn btn-g btn-sm" href="?edit=<?= $h['id'] ?>">Edit</a>
      <form method="post" class="inline" data-confirm="Delete this hospital and its login?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $h['id'] ?>"><button class="btn btn-d btn-sm" name="action" value="delete">Delete</button></form></div></td></tr>
  <?php endforeach; ?></table></div></div>
<?php page_end();
