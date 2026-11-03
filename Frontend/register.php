<?php
require __DIR__ . '/../Backend/config/config.php';
require __DIR__ . '/../Backend/includes/layout.php';
if ($u = me()) redirect(home_for($u['role']));
$role = ($_GET['role'] ?? $_POST['role'] ?? 'parent') === 'hospital' ? 'hospital' : 'parent';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = post('name'); $email = strtolower(post('email')); $phone = post('phone');
    $address = post('address'); $city = post('city'); $pass = (string)($_POST['password'] ?? '');
    if (!valid_name($name)) $errors[] = 'Use a full name with letters, spaces, dots, apostrophes, or hyphens only.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    elseif (q('SELECT 1 FROM users WHERE email=?', [$email])->fetch()) $errors[] = 'That email is already registered.';
    if (!valid_phone($phone)) $errors[] = 'Enter a valid phone number using digits and optional +, spaces, hyphens, or parentheses.';

    if (!valid_city($city)) $errors[] = 'Enter a valid city name with letters, spaces, dots, apostrophes, or hyphens only.';
    if (!valid_password($pass)) $errors[] = 'Password must be 8+ characters with letters and numbers.';
    if ($pass !== (string)($_POST['confirm'] ?? '')) $errors[] = 'Passwords do not match.';
    if (!$errors) {
        $pdo = db(); $pdo->beginTransaction();
        q('INSERT INTO users(name,email,phone,password,role) VALUES(?,?,?,?,?)', [$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT), $role]);
        $uid = (int)$pdo->lastInsertId();
        if ($role === 'parent') q('INSERT INTO parents(user_id,address,city) VALUES(?,?,?)', [$uid, $address, $city]);
        else q("INSERT INTO hospitals(user_id,name,address,city,phone,status) VALUES(?,?,?,?,?,'pending')", [$uid, $name, $address, $city, $phone]);
        $pdo->commit();
        if ($role === 'parent') {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $uid, 'name' => $name, 'email' => $email, 'role' => 'parent'];
            flash('Account created. Add your child to get their vaccination schedule.');
            redirect('parent/children.php');
        }
        flash('Hospital registered. You can log in once the admin approves your account.');
        redirect('login.php');
    }
}
public_start('Create account');
?>
<div class="auth auth-signup">
  <aside class="auth-hero">
    <div class="auth-badge">Create your account</div>
    <h2>Join <?= APP_NAME ?></h2>
    <p><?= $role === 'parent' ? 'Parents and adults get a personal vaccination schedule, travel clearance and online hospital booking.' : 'Hospitals receive approved appointments and record each vaccination. The admin reviews every new hospital.' ?></p>
    <ul class="feature-list">
      <li>Track due vaccines in one place</li>
      <li>Book a hospital in minutes</li>
      <li>Stay updated on travel clearance</li>
    </ul>
  </aside>
  <main class="auth-panel">
    <div class="auth-topbar">
      <span class="auth-pill">New registration</span>
    </div>
    <h1>Create account</h1>
    <div class="tabs"><a class="<?= $role === 'parent' ? 'on' : '' ?>" href="?role=parent">Parent or adult</a><a class="<?= $role === 'hospital' ? 'on' : '' ?>" href="?role=hospital">Hospital</a></div>
    <?php foreach ($errors as $er): ?><div class="alert bad"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" class="fgrid auth-form">
      <?= csrf_field() ?><input type="hidden" name="role" value="<?= $role ?>">
      <?php
      field($role === 'parent' ? 'Full name' : 'Hospital name', 'name', 'text', ['class' => 'wide', 'attr' => 'maxlength="80" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s.\'-]{3,}$" title="Use letters, spaces, dots, apostrophes, or hyphens only."']);
      field('Email address', 'email', 'email', ['attr' => 'inputmode="email" maxlength="255" pattern="^[^\s@]+@[^\s@]+\\.[^\s@]+$" title="Use a valid email address, like name@example.com."']);
      field('Phone', 'phone', 'tel', ['attr' => 'maxlength="20" pattern="[0-9+()\-\s]{7,20}" title="Use a valid phone number with digits and optional +, spaces, hyphens, or parentheses."']);
        field($role === 'parent' ? 'Address' : 'Hospital address', 'address', 'text', ['class' => 'wide', 'attr' => 'maxlength="120"']);
      field('City', 'city', 'text', ['class' => 'wide', 'attr' => 'maxlength="60" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s.\'-]{2,}$" title="Use letters, spaces, dots, apostrophes, or hyphens only."']);
      field('Password', 'password', 'password', ['attr' => 'minlength="8" autocomplete="new-password" pattern="(?=.*[A-Za-z])(?=.*\\d).{8,}" title="Password must be at least 8 characters and include letters and numbers."']);
      field('Confirm password', 'confirm', 'password', ['attr' => 'autocomplete="new-password" minlength="8" pattern="(?=.*[A-Za-z])(?=.*\\d).{8,}" title="Confirm the password exactly as entered above."']);
      ?>
      <button class="btn btn-p wide" type="submit">Create account</button>
    </form>
    <div class="auth-switch-wrap">
      <span>Already registered?</span>
      <a class="auth-switch-btn" href="<?= url('login.php') ?>">Log in</a>
    </div>
  </main>
</div>
<?php public_end();
