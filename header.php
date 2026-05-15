<?php require_once __DIR__.'/config/auth.php'; $user=current_user(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Link-Locker</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
  <div class="topbar">
    <a href="<?= $user ? 'dashboard.php' : 'index.php' ?>" class="brand">
      <div class="logo">🔒</div>
      <div>Link-Locker</div>
    </a>
    <div class="actions">
      <?php if($user): ?>
        <span class="chip">@<?= e($user['username']) ?></span>
        <a class="btn ghost" href="u.php?username=<?= urlencode($user['username']) ?>" target="_blank">Public page</a>
        <a class="btn ghost" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn ghost" href="login.php">Login</a>
        <a class="btn" href="register.php">Get started</a>
      <?php endif; ?>
    </div>
  </div>