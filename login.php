<?php
require_once __DIR__.'/config/auth.php';
if(current_user()){header('Location: dashboard.php');exit;}
$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $identifier = trim($_POST['identifier'] ?? '');
    $password   =      $_POST['password']   ?? '';

    // email অথবা username — যেটা দিয়েই login করুক
    $stmt = db()->prepare(
        'SELECT * FROM users WHERE email=? OR username=? LIMIT 1'
    );
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    if(!$u || !password_verify($password, $u['password_hash'])){
        $error = 'Invalid email/username or password.';
    } else {
        $_SESSION['user'] = [
            'id'       => $u['id'],
            'username' => $u['username'],
            'email'    => $u['email']
        ];
        header('Location: dashboard.php');exit;
    }
}
require_once __DIR__.'/header.php';
?>

<div class="auth-wrap">
  <div class="auth-card panel">

    <div class="auth-logo">
      <div class="logo">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
    </div>

    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-sub">Log in with your email or username</p>

    <?php if($error): ?>
    <div class="error auth-notice">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="post" class="auth-form">

      <div class="field">
        <label>Email or Username</label>
        <div class="input-wrap">
          <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <input
            class="input input-iconed"
            type="text"
            name="identifier"
            placeholder="you@example.com or yourname"
            required
            autocomplete="username">
        </div>
      </div>

      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <input
            class="input input-iconed"
            type="password"
            name="password"
            placeholder="••••••••"
            required
            autocomplete="current-password">
        </div>
      </div>

      <button class="btn btn-cta auth-btn" type="submit">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
          <polyline points="10 17 15 12 10 7"/>
          <line x1="15" y1="12" x2="3" y2="12"/>
        </svg>
        Log in
      </button>

    </form>

    <div class="auth-footer">
      Don't have an account?
      <a href="register.php" class="auth-link">Create one free →</a>
    </div>

  </div>
</div>

<?php require_once __DIR__.'/footer.php'; ?>