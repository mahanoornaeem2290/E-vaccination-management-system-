<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
$u = require_role('parent');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (post('action') === 'password') {
        $row = q('SELECT password FROM users WHERE id=?', [$u['id']])->fetchColumn();
        if (!password_verify((string)($_POST['current'] ?? ''), $row)) $errors[] = 'Your current password is incorrect.';
        elseif (!valid_password((string)($_POST['new'] ?? ''))) $errors[] = 'New password needs 8+ characters with letters and numbers.';
        elseif ($_POST['new'] !== ($_POST['confirm'] ?? '')) $errors[] = 'New passwords do not match.';
        else { q('UPDATE users SET password=? WHERE id=?', [password_hash($_POST['new'], PASSWORD_DEFAULT), $u['id']]); flash('Password changed.'); redirect('parent/profile.php'); }
    } else {
        if (mb_strlen(post('name')) < 3) $errors[] = 'Enter your name.';
        if (!valid_phone(post('phone'))) $errors[] = 'Enter a valid phone number.';
        if (!$errors) {
            q('UPDATE users SET name=?,phone=? WHERE id=?', [post('name'), post('phone'), $u['id']]);
            q('UPDATE parents SET address=?,city=? WHERE user_id=?', [post('address'), post('city'), $u['id']]);
            $_SESSION['user']['name'] = post('name'); flash('Profile saved.'); redirect('parent/profile.php');
        }
    }
}
$p = q('SELECT u.name,u.email,u.phone,p.address,p.city FROM users u JOIN parents p ON p.user_id=u.id WHERE u.id=?', [$u['id']])->fetch();
$v = fn($k) => ['value' => $_POST[$k] ?? $p[$k]];
page_start('My profile', 'parent');
foreach ($errors as $er): ?><div class="alert bad"><?= e($er) ?></div><?php endforeach; ?>
<div class="cols"><div class="card"><h2>Details</h2><form method="post" class="fgrid"><?= csrf_field() ?>
  <?php field('Full name', 'name', 'text', $v('name')); field('Phone', 'phone', 'tel', $v('phone')); field('Address', 'address', 'text', $v('address') + ['required' => false]); field('City', 'city', 'text', $v('city') + ['required' => false]); ?>
  <p class="muted wide">Email: <?= e($p['email']) ?></p><button class="btn btn-p wide">Save profile</button></form></div>
<div class="card"><h2>Change password</h2><form method="post" class="fgrid" autocomplete="off"><?= csrf_field() ?><input type="hidden" name="action" value="password">
  <?php field('Current password', 'current', 'password', ['class' => 'wide', 'attr' => 'autocomplete="new-password"']); field('New password', 'new', 'password', ['attr' => 'minlength="8" autocomplete="new-password"']); field('Confirm new password', 'confirm', 'password', ['attr' => 'autocomplete="new-password"']); ?>
  <button class="btn btn-p wide">Change password</button></form></div></div>
<?php page_end();
