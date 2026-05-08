<?php
require_once __DIR__.'/config/auth.php'; require_login();
require_once __DIR__.'/components.php';
$user = current_user();
$stmt = db()->prepare('SELECT * FROM cards WHERE user_id=? ORDER BY created_at DESC');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$cards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$ok = $_GET['ok'] ?? '';
require_once __DIR__.'/header.php';
?>

<!-- ── Toolbar ── -->
<div class="toolbar">
  <div class="toolbar-left">
    <div class="search-wrap">
      <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input class="input search-input" id="search" placeholder="Search your vault…">
    </div>
    <select id="filterType" class="input filter-select">
      <option value="">All types</option>
      <option>Link</option>
      <option>Image</option>
      <option>PDF</option>
      <option>Note</option>
    </select>
    <select id="filterVis" class="input filter-select">
      <option value="">All visibility</option>
      <option>Public</option>
      <option>Private</option>
    </select>
  </div>
  <div class="toolbar-right">
    <span class="chip toolbar-hint">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
      Press + to quick-save
    </span>
    <button class="btn btn-cta" data-open-modal>
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
      New item
    </button>
  </div>
</div>

<?php if($ok): ?>
  <div class="notice">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0"><path d="M20 6 9 17l-5-5"/></svg>
    <?= e($ok) ?>
  </div>
<?php endif; ?>

<?php if(!$cards): ?>
  <!-- ── Empty state ── -->
  <div class="panel empty-state">
    <div class="empty-icon">🔒</div>
    <h3 class="empty-title">Your vault is empty</h3>
    <p class="empty-desc">Click the button below, paste a useful link,<br>preview it instantly, and save it forever.</p>
    <button class="btn btn-cta" data-open-modal>
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
      Add your first item
    </button>
  </div>
<?php else: ?>
  <!-- ── Stats bar ── -->
  <div class="stats-bar">
    <?php
      $total  = count($cards);
      $links  = count(array_filter($cards, fn($c)=>$c['type']==='Link'));
      $images = count(array_filter($cards, fn($c)=>$c['type']==='Image'));
      $pdfs   = count(array_filter($cards, fn($c)=>$c['type']==='PDF'));
      $notes  = count(array_filter($cards, fn($c)=>$c['type']==='Note'));
      $pub    = count(array_filter($cards, fn($c)=>$c['visibility']==='Public'));
    ?>
    <div class="stat-pill"><strong><?= $total ?></strong><span>Total</span></div>
    <div class="stat-pill"><strong><?= $links ?></strong><span>Links</span></div>
    <div class="stat-pill"><strong><?= $images ?></strong><span>Images</span></div>
    <div class="stat-pill"><strong><?= $pdfs ?></strong><span>PDFs</span></div>
    <div class="stat-pill"><strong><?= $notes ?></strong><span>Notes</span></div>
    <div class="stat-pill stat-pub"><strong><?= $pub ?></strong><span>Public</span></div>
  </div>

  <!-- ── Cards grid ── -->
  <div class="grid" id="vaultGrid">
    <?php foreach($cards as $card) render_card($card, true); ?>
  </div>
<?php endif; ?>

<!-- ── FAB ── -->
<button class="fab" data-open-modal aria-label="Add new item">+</button>

<!-- ── Modal ── -->
<div class="modal" id="quickModal">
  <div class="modal-box panel" onclick="event.stopPropagation()">

    <!-- Form side -->
    <form method="post" action="save.php" enctype="multipart/form-data" class="modal-form panel">
      <input type="hidden" name="type" id="type" value="Link">

      <div class="modal-header">
        <div>
          <h2 class="modal-title">Quick lock</h2>
          <p class="helper">Paste a link or add a file / note. Details are optional.</p>
        </div>
        <button type="button" class="icon-btn" data-close-modal aria-label="Close">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="tabs">
        <button type="button" class="tab active" data-mode="link">🔗 Link</button>
        <button type="button" class="tab" data-mode="upload">🖼️ Upload</button>
        <button type="button" class="tab" data-mode="note">📝 Note</button>
      </div>

      <div id="mode-link">
        <div class="field">
          <label>Paste a link</label>
          <input class="input" id="link_url" name="link_url" placeholder="https://example.com/article">
        </div>
      </div>

      <div id="mode-upload" class="hidden">
        <div class="field">
          <label>Choose image or PDF</label>
          <input class="input" id="file" type="file" name="file" accept="image/*,application/pdf">
        </div>
      </div>

      <div id="mode-note" class="hidden">
        <div class="field">
          <label>Write a note</label>
          <textarea id="note_content" name="note_content" rows="6" placeholder="Write something useful…"></textarea>
        </div>
      </div>

      <div class="two-col">
        <div class="field">
          <label>Title <span class="small">optional</span></label>
          <input class="input" id="title" name="title" placeholder="Auto-generated if empty">
        </div>
        <div class="field">
          <label>Visibility</label>
          <select id="visibility" name="visibility">
            <option>Private</option>
            <option>Public</option>
          </select>
        </div>
      </div>

      <div class="field">
        <label>Description <span class="small">optional</span></label>
        <textarea id="description" name="description" rows="2" placeholder="Short reminder…"></textarea>
      </div>

      <div class="two-col">
        <div class="field">
          <label>Category <span class="small">optional</span></label>
          <input class="input" name="category" placeholder="Study, Tools, Design…">
        </div>
        <div class="field">
          <label>Tags <span class="small">optional</span></label>
          <input class="input" name="tags" placeholder="read-later, useful…">
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn btn-cta" type="submit">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Save to vault
        </button>
        <button class="btn ghost" type="button" data-close-modal>Cancel</button>
      </div>
    </form>

    <!-- Preview side -->
    <div class="panel preview-card card">
      <div class="preview" id="pv-stage">
        <div class="preview-inner">
          <div style="font-size:52px">🔒</div>
          <div class="muted" style="font-size:13px">Live preview appears here.</div>
        </div>
      </div>
      <div class="card-body">
        <div class="meta">
          <span class="tag" id="pv-type">Link</span>
          <span class="tag" id="pv-visibility">Private</span>
        </div>
        <h3 class="card-title" id="pv-title">Untitled item</h3>
        <div class="desc" id="pv-desc">Preview updates as you type.</div>
        <div class="small" style="margin-top:auto">Preview-first saving experience.</div>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__.'/footer.php'; ?>