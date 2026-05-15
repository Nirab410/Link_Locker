<?php
require_once __DIR__.'/config/auth.php';
require_once __DIR__.'/components.php';

$username = trim($_GET['username'] ?? '');
$us = db()->prepare('SELECT id,username FROM users WHERE username=? LIMIT 1');
$us->bind_param('s', $username);
$us->execute();
$profile = $us->get_result()->fetch_assoc();

// Public card count
$total_public = 0;
$cards = [];
if($profile){
    $stmt = db()->prepare('SELECT * FROM cards WHERE user_id=? AND visibility="Public" ORDER BY created_at DESC');
    $stmt->bind_param('i', $profile['id']);
    $stmt->execute();
    $cards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $total_public = count($cards);
}

require_once __DIR__.'/header.php';
?>

<?php if(!$profile): ?>

  <!-- ── User not found ── -->
  <div class="panel profile-empty">
    <div class="profile-empty-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
    </div>
    <h3 class="profile-empty-title">User not found</h3>
    <p class="profile-empty-desc">This profile doesn't exist or may have been removed.</p>
    <a class="btn ghost" href="index.php">← Back to home</a>
  </div>

<?php else: ?>

  <!-- ── Profile header ── -->
  <div class="profile-header panel">
    <div class="profile-avatar">
      <?= strtoupper(mb_substr($profile['username'], 0, 1)) ?>
    </div>
    <div class="profile-info">
      <div class="profile-badge">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        Public vault
      </div>
      <h2 class="profile-name">@<?= e($profile['username']) ?></h2>
      <p class="profile-sub">Sharing their saved links, notes and files on Link-Locker.</p>
    </div>
    <div class="profile-stat">
      <strong><?= $total_public ?></strong>
      <span>Public item<?= $total_public !== 1 ? 's' : '' ?></span>
    </div>
  </div>

  <?php if(!$cards): ?>

    <!-- ── No public items ── -->
    <div class="panel profile-empty">
      <div class="profile-empty-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
      <h3 class="profile-empty-title">No public items yet</h3>
      <p class="profile-empty-desc">@<?= e($profile['username']) ?> hasn't made anything public yet.</p>
    </div>

  <?php else: ?>

    <!-- ── Cards grid ── -->
    <div class="grid" id="vaultGrid">
      <?php foreach($cards as $c) render_card($c, false); ?>
    </div>

  <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__.'/footer.php'; ?>