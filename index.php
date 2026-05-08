<?php require_once __DIR__.'/header.php'; ?>

<section class="hero panel">
  <div class="hero-left">
    <div class="hero-badge">
      <span class="hero-dot"></span>
      Personal content vault
    </div>
    <h1 class="hero-title">Your links.<br><span class="hero-grad">Beautifully</span> organised.</h1>
    <p class="hero-sub">Save links, images, PDFs and notes as visual cards — with instant preview. Always searchable, always yours.</p>
    <div class="actions hero-actions">
      <a class="btn btn-cta" href="register.php">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Create account
      </a>
      <a class="btn ghost" href="login.php">Log in →</a>
    </div>
    <div class="hero-pills">
      <span class="hero-pill">🔗 Links</span>
      <span class="hero-pill">🖼️ Images</span>
      <span class="hero-pill">📄 PDFs</span>
      <span class="hero-pill">📝 Notes</span>
    </div>
  </div>

  <div class="hero-right">
    <div class="demo-window">
      <div class="demo-bar">
        <span class="dm-dot dm-r"></span>
        <span class="dm-dot dm-y"></span>
        <span class="dm-dot dm-g"></span>
        <span class="demo-bar-title">link-locker · my vault</span>
      </div>
      <div class="demo-grid">
        <div class="demo-card panel card">
          <div class="preview" style="height:80px"><div class="preview-inner" style="justify-content:center;font-size:28px">🔗</div><span class="file-badge">Link</span><span class="visibility">🔒</span></div>
          <div class="card-body" style="padding:10px 12px;gap:4px"><div class="card-title" style="font-size:13px">GitHub Repo</div><div class="desc" style="font-size:11px">github.com</div></div>
        </div>
        <div class="demo-card panel card">
          <div class="preview" style="height:80px;background:linear-gradient(135deg,rgba(6,182,212,.2),rgba(16,185,129,.1))"><div class="preview-inner" style="justify-content:center;font-size:28px">🖼️</div><span class="file-badge">Image</span><span class="visibility">🌐</span></div>
          <div class="card-body" style="padding:10px 12px;gap:4px"><div class="card-title" style="font-size:13px">UI Screenshot</div><div class="desc" style="font-size:11px">Saved today</div></div>
        </div>
        <div class="demo-card panel card">
          <div class="preview" style="height:80px;background:linear-gradient(135deg,rgba(239,68,68,.15),rgba(249,115,22,.1))"><div class="preview-inner" style="justify-content:center;font-size:28px">📄</div><span class="file-badge">PDF</span><span class="visibility">🔒</span></div>
          <div class="card-body" style="padding:10px 12px;gap:4px"><div class="card-title" style="font-size:13px">Design Brief</div><div class="desc" style="font-size:11px">2.4 MB · PDF</div></div>
        </div>
        <div class="demo-card panel card">
          <div class="preview" style="height:80px;background:linear-gradient(135deg,rgba(245,158,11,.15),rgba(234,179,8,.1))"><div class="preview-inner" style="justify-content:center;font-size:28px">📝</div><span class="file-badge">Note</span><span class="visibility">🌐</span></div>
          <div class="card-body" style="padding:10px 12px;gap:4px"><div class="card-title" style="font-size:13px">Quick idea</div><div class="desc" style="font-size:11px">Yesterday</div></div>
        </div>
      </div>
      <div class="demo-stats">
        <div class="demo-stat"><strong>24</strong><span>Links</span></div>
        <div class="demo-stat"><strong>11</strong><span>Images</span></div>
        <div class="demo-stat"><strong>8</strong><span>Notes</span></div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__.'/footer.php'; ?>