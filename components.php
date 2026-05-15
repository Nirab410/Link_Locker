<?php

function clean_link_title($raw_title, $url) {
    $raw = trim($raw_title ?? '');
    // যদি empty অথবা শুধু alphanumeric/hash হয় (কোনো space বা readable word নেই)
    $is_garbage = empty($raw)
        || preg_match('/^[a-zA-Z0-9_\-]{20,}$/', $raw)   // slug/hash with no spaces
        || preg_match('/^[a-f0-9]{8,}$/i', str_replace('-','',$raw)); // hex hash

    if ($is_garbage) {
        $path  = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $parts = array_filter(explode('/', $path));
        $slug  = '';
        foreach (array_reverse($parts) as $p) {
            $clean = str_replace('-', '', $p);
            // skip if hex hash or pure number
            if (preg_match('/^[a-f0-9]{16,}$/i', $clean)) continue;
            if (preg_match('/^\d+$/', $p)) continue;
            if (strlen($p) < 2) continue;
            $slug = $p;
            break;
        }
        if ($slug) {
            $slug = preg_replace('/\.[a-z]{2,5}$/i', '', $slug);
            $title = ucwords(str_replace(['-','_'], ' ', rawurldecode($slug)));
            return mb_strimwidth($title, 0, 52, '…');
        }
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        return mb_strimwidth(preg_replace('/^www\./', '', $host) ?: 'Untitled link', 0, 52, '…');
    }
    return mb_strimwidth($raw, 0, 52, '…');
}


function card_preview($card) {
    $type = $card['type'] ?? '';

    if ($type === 'Image' && !empty($card['file_path'])) {
        return '<img src="'.e($card['file_path']).'" alt="'.e($card['title'] ?? '').'" style="width:100%;height:100%;object-fit:cover;display:block">';
    }

    if ($type === 'PDF') {
        $name = e($card['original_filename'] ?? 'PDF Document');
        return '
        <div class="preview-inner preview-pdf">
            <div class="prev-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
            <div class="prev-label">PDF Document</div>
            <div class="prev-filename">'.$name.'</div>
        </div>';
    }

    if ($type === 'Link' && !empty($card['link_url'])) {
        $url    = $card['link_url'];
        $host   = parse_url($url, PHP_URL_HOST) ?? '';
        $domain = preg_replace('/^www\./', '', $host) ?: 'link';
        $icon   = 'https://www.google.com/s2/favicons?sz=128&domain_url='.rawurlencode($url);
        $display_title = clean_link_title($card['title'] ?? '', $url);
        return '
        <div class="preview-inner preview-link">
            <div class="prev-link-body">
                <img src="'.e($icon).'" alt="" class="prev-favicon"
                     onerror="this.style.display=\'none\'">
                <div class="prev-link-info">
                    <div class="prev-link-title">'.e($display_title).'</div>
                    <div class="prev-domain">'.e($domain).'</div>
                </div>
            </div>
        </div>';
    }

    if ($type === 'Note') {
        $text = trim($card['note_content'] ?? $card['description'] ?? 'Quick note');
        return '
        <div class="preview-inner preview-note">
            <div class="prev-note-label">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                Quick note
            </div>
            <div class="prev-note-text">'.e(mb_strimwidth($text, 0, 180, '…')).'</div>
        </div>';
    }

    return '
    <div class="preview-inner">
        <div class="prev-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
        </div>
        <div class="prev-label muted">Saved item</div>
    </div>';
}


function render_card($card, $owner = true) {
    $type       = $card['type']       ?? '';
    $visibility = $card['visibility'] ?? 'Private';
    $is_public  = $visibility === 'Public';
    $date       = !empty($card['created_at']) ? date('d M Y', strtotime($card['created_at'])) : '';
    $url        = $card['link_url'] ?? '';

    $display_title = ($type === 'Link' && !empty($url))
        ? clean_link_title($card['title'] ?? '', $url)
        : mb_strimwidth(trim($card['title'] ?? 'Untitled'), 0, 52, '…');
    if (!$display_title) $display_title = 'Untitled';

    $search = strtolower(trim(
        ($card['title']        ?? '').' '.
        ($card['description']  ?? '').' '.
        ($card['category']     ?? '').' '.
        ($card['tags']         ?? '').' '.
        $url.' '.
        ($card['note_content'] ?? '')
    ));

    $type_colors = [
        'Link'  => 'tc-link',
        'Image' => 'tc-image',
        'PDF'   => 'tc-pdf',
        'Note'  => 'tc-note',
    ];
    $tc = $type_colors[$type] ?? 'tc-link';

    $type_svg = [
        'Link'  => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
        'Image' => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>',
        'PDF'   => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
        'Note'  => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
    ];
    $icon = $type_svg[$type] ?? '';
    $tags = array_filter(array_map('trim', explode(',', $card['tags'] ?? '')));
?>
<article class="card vault-item <?= $tc ?>"
         data-search="<?= e($search) ?>"
         data-type="<?= e($type) ?>"
         data-visibility="<?= e($visibility) ?>">

    <div class="card-preview-wrap">
        <!-- Gradient overlay top -->
        <span class="file-badge">
    <?= $icon ?> <?= e($type) ?>
</span>
<span class="vis-badge vis-<?= strtolower(e($visibility)) ?>">
    <?php if($is_public): ?>
        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
    <?php else: ?>
        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    <?php endif; ?>
    <?= e($visibility) ?>
</span>

        <!-- Preview content -->
        <div class="card-preview-inner">
            <?= card_preview($card) ?>
        </div>
    </div>

    <div class="card-body">
        <h3 class="card-title"><?= e($display_title) ?></h3>

        <?php if (!empty($card['description'])): ?>
            <div class="desc"><?= e(mb_strimwidth($card['description'], 0, 80, '…')) ?></div>
        <?php endif; ?>

        <?php if (!empty($card['category']) || !empty($tags)): ?>
        <div class="meta">
            <?php if (!empty($card['category'])): ?>
                <span class="tag tag-category">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <?= e($card['category']) ?>
                </span>
            <?php endif; ?>
            <?php foreach ($tags as $tag): ?>
                <span class="tag">#<?= e($tag) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="card-footer">
            <div class="card-date">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?= e($date) ?>
            </div>
            <div class="card-actions">
                <?php if ($type === 'Link' && !empty($url)): ?>
                    <a class="icon-btn icon-btn-open" target="_blank" href="<?= e($url) ?>">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Open
                    </a>
                <?php endif; ?>
                <?php if ($type === 'PDF' && !empty($card['file_path'])): ?>
                    <a class="icon-btn icon-btn-open" target="_blank" href="<?= e($card['file_path']) ?>">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Open PDF
                    </a>
                <?php endif; ?>
                <?php if ($owner): ?>
                    <a class="icon-btn icon-btn-vis" href="toggle.php?id=<?= (int)$card['id'] ?>">
                        <?php if ($is_public): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Make Private
                        <?php else: ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            Make Public
                        <?php endif; ?>
                    </a>
                    <a class="icon-btn icon-btn-del"
                       href="delete.php?id=<?= (int)$card['id'] ?>"
                       onclick="return confirm('Delete this item permanently?')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>
<?php }
?>