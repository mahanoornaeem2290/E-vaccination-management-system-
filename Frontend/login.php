<?php
/**
 * User Authentication Controller
 * 
 * Handles multi-role login (Parent, Hospital, Admin).
 * Validates credentials via password_verify against Bcrypt hashes,
 * verifies hospital approval status, regenerates session IDs against fixation attacks,
 * and routes authenticated users to their corresponding role dashboard.
 */

require __DIR__ . '/../Backend/config/config.php';
require __DIR__ . '/../Backend/includes/layout.php';

// Redirect already authenticated users to their home dashboard
if ($u = me()) {
    redirect(home_for($u['role']));
}

$err = '';

// Process incoming authentication submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate Anti-CSRF Token
    csrf_check();

    // 2. Extract and normalize input fields
    $email = strtolower(post('email'));
    $password = (string)($_POST['password'] ?? '');

    // 3. Validate input formatting
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Enter a valid email address.';
    } elseif ($password === '' || !valid_password($password)) {
        $err = 'Enter a valid password with at least 8 characters and both letters and numbers.';
    } else {
        // 4. Retrieve user record from database
        $u = q('SELECT * FROM users WHERE email=?', [$email])->fetch();

        // 5. Verify cryptographic password hash
        if (!$u || !password_verify($password, $u['password'])) {
            $err = 'The email or password is incorrect.';
        } else {
            // 6. Check hospital verification status if applicable
            if ($u['role'] === 'hospital') {
                $st = q('SELECT status FROM hospitals WHERE user_id=?', [$u['id']])->fetchColumn();
                if ($st === 'pending')  $err = 'Your hospital registration is waiting for admin approval.';
                if ($st === 'rejected') $err = 'Your hospital registration was not approved. Please contact the admin.';
            }

            // 7. Initialize authenticated user session upon successful validation
            if (!$err) {
                session_regenerate_id(true); // Mitigate session fixation attacks
                $_SESSION['user'] = [
                    'id'    => (int)$u['id'],
                    'name'  => $u['name'],
                    'email' => $u['email'],
                    'role'  => $u['role']
                ];
                redirect(home_for($u['role']));
            }
        }
    }
}

public_start('Log in');
?>
<div class="auth auth-login">
  <aside class="auth-hero">
    <div class="auth-badge">Secure access</div>
    <h2>Welcome back</h2>
    <p>Log in to track vaccination schedules, approve appointments, and manage travel clearance.</p>
    <div class="demo-grid">
    </div>
  </aside>
  <main class="auth-panel">
    <div class="auth-topbar">
      <span class="auth-pill">Member login</span>
    </div>
    <h1>Log in</h1>
    <?php if ($err): ?><div class="alert bad"><?= e($err) ?></div><?php endif; ?>
    <form method="post" class="fgrid auth-form" id="loginForm">
      <?= csrf_field() ?>
      <?php field('Email address', 'email', 'email', ['attr' => 'autocomplete="username" inputmode="email" maxlength="255" pattern="^[^\s@]+@[^\s@]+\\.[^\s@]+$" title="Use a valid email address, like name@example.com."']);
      field('Password', 'password', 'password', ['attr' => 'autocomplete="current-password" minlength="8" pattern="(?=.*[A-Za-z])(?=.*\\d).{8,}" title="Password must be at least 8 characters and include letters and numbers."']); ?>
      <button class="btn btn-p wide" type="submit">Log in</button>
    </form>
    <div class="auth-switch-wrap">
      <span>New here?</span>
      <a class="auth-switch-btn" href="<?= url('register.php') ?>">Create an account</a>
    </div>
  </main>
</div>
<?php public_end();
