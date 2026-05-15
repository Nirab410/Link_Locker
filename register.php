<?php
require_once __DIR__.'/config/auth.php';
if(current_user()){header('Location: dashboard.php');exit;}
$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    if(!$username || !$email || !$password){
        $error = 'Please fill in all fields.';
    } else {
        $stmt = db()->prepare('INSERT INTO users (username,email,password_hash) VALUES (?,?,?)');
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param('sss', $username, $email, $hash);
        if(!$stmt->execute()){
            $error = 'Username or email already exists.';
        } else {
            $_SESSION['user'] = ['id'=>$stmt->insert_id,'username'=>$username,'email'=>$email];
            header('Location: dashboard.php');exit;
        }
    }
}
require_once __DIR__.'/header.php';
?>

<div class="auth-wrap">
  <div class="auth-card panel">

    <!-- Logo -->
    <div class="auth-logo">
      <div class="logo">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
    </div>

    <h2 class="auth-title">Create your vault</h2>
    <p class="auth-sub">Save links, images, PDFs & notes — visually.</p>

    <?php if($error): ?>
    <div class="error auth-notice">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="post" class="auth-form">
      <div class="field">
        <label>Username</label>
        <div class="input-wrap">
          <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <input class="input input-iconed" name="username" placeholder="yourname" required>
        </div>
      </div>

      <div class="field">
        <label>Email address</label>
        <div class="input-wrap">
          <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <input class="input input-iconed" type="email" name="email" placeholder="you@example.com" required>
        </div>
      </div>

      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <input class="input input-iconed" type="password" name="password" placeholder="••••••••" required>
        </div>
      </div>

      <button class="btn btn-cta auth-btn" type="submit">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Create account
      </button>
    </form>

    <div class="auth-divider">
      <span>Free forever · No credit card</span>
    </div>

    <div class="auth-footer">
      Already have an account?
      <a href="login.php" class="auth-link">Log in →</a>
    </div>

  </div>
</div>

<?php require_once __DIR__.'/footer.php'; ?>