<?php
if (!function_exists('renderKeepCard')) {
    function renderKeepCard(array $note): void {
        $color = $note['note_color'] ?? 'white';
        $tagNames = array_map(static fn(array $tag): string => $tag['name'], $note['tags'] ?? []);
        $tagIds = array_map(static fn(array $tag): int => (int)$tag['id'], $note['tags'] ?? []);
        $copyText = trim($note['title'] . "\n\n" . $note['content']);
        $priority = $note['priority'] ?? 'Normal';
        ?>
        <article class="note-card keep-card keep-color-<?= e($color) ?> <?= !empty($note['is_pinned']) ? 'is-pinned' : '' ?>"
            data-keep-card
            data-note-id="<?= e($note['id']) ?>"
            data-note-title="<?= e($note['title']) ?>"
            data-note-content="<?= e($note['content']) ?>"
            data-note-color="<?= e($color) ?>"
            data-note-pinned="<?= !empty($note['is_pinned']) ? '1' : '0' ?>"
            data-note-category-id="<?= e($note['category_id'] ?? '') ?>"
            data-note-tag-ids="<?= e(json_encode($tagIds)) ?>"
            data-note-priority="<?= e($priority) ?>"
            data-note-is-archived="1">
            
            <button class="keep-icon-btn pin-toggle-btn note-pin-btn" type="button" data-note-id="<?= e($note['id']) ?>" title="<?= !empty($note['is_pinned']) ? 'Unpin' : 'Pin' ?>" aria-label="Pin">
                <?= !empty($note['is_pinned']) ? '●' : '○' ?>
            </button>

            <div class="keep-card-main">
                <?php if ($note['image_path']): ?>
                    <img class="note-thumb keep-image" src="<?= e(upload_url($note['image_path'])) ?>" alt="<?= e($note['title']) ?>" data-card-image>
                <?php else: ?>
                    <img class="note-thumb keep-image" src="" alt="" data-card-image hidden>
                <?php endif; ?>
                
                <div class="keep-card-text">
                    <div class="note-meta">
                        <?php if (!empty($note['is_pinned'])): ?><span class="badge badge-pin">Pinned</span><?php endif; ?>
                        <?php if (!empty($note['is_favorite'])): ?><span class="badge">Favorite</span><?php endif; ?>
                        <?php if ($priority === 'Important'): ?><span class="badge" style="background-color: #f28b82; color: #5c2b29; font-weight:bold;">Important</span><?php endif; ?>
                        <?php if (!empty($note['is_shared'])): ?><span class="badge">Shared</span><?php endif; ?>
                        <span class="badge" style="background-color: #e8f0fe; color: #1a73e8;">Archived</span>
                    </div>
                    <h2 data-card-title><?= e($note['title']) ?></h2>
                    <p data-card-content><?= e(strlen($note['content']) > 240 ? substr($note['content'], 0, 240) . '...' : $note['content']) ?></p>
                </div>
                
                <div class="tag-row keep-tags" data-card-tags>
                    <?php if (!empty($note['category_name'])): ?>
                        <span class="category-pill"><?= e($note['category_name']) ?></span>
                    <?php endif; ?>
                    <?php foreach ($note['tags'] ?? [] as $tag): ?>
                        <span data-tag-id="<?= e($tag['id']) ?>">#<?= e($tag['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="keep-actionbar">
                <div class="keep-popover-wrap">
                    <button class="keep-icon-btn" type="button" title="Background color" data-keep-toggle="color">🎨</button>
                    <div class="keep-popover color-popover" data-keep-popover="color">
                        <?php foreach (['white', 'yellow', 'green', 'blue', 'pink', 'purple'] as $swatch): ?>
                            <button class="color-swatch keep-color-<?= e($swatch) ?>" type="button" title="<?= e(ucfirst($swatch)) ?>" data-note-color="<?= e($swatch) ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="keep-icon-btn" type="button" title="Remind me" data-toast-message="Reminder set">🔔</button>
                <button class="keep-icon-btn" type="button" title="Collaborator" data-toast-message="Collaboration feature coming soon">👤</button>
                <label class="keep-icon-btn file-icon-btn" title="Add image">
                    🖼
                    <input type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-card-image-input hidden>
                </label>
                <button class="keep-icon-btn archive-btn" type="button" title="Unarchive" data-note-action="archive" data-note-id="<?= e($note['id']) ?>">📤</button>
                <div class="keep-popover-wrap">
                    <button class="keep-icon-btn" type="button" title="More options" data-keep-toggle="more">⋮</button>
                    <div class="keep-popover keep-menu" data-keep-popover="more">
                        <form method="post" action="<?= e(url('notes/delete')) ?>" data-confirm="Move this archived note to the recycle bin?" style="display:none;" id="form-delete-<?= e($note['id']) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($note['id']) ?>">
                        </form>
                        <button type="button" onclick="event.stopPropagation(); document.getElementById('form-delete-<?= e($note['id']) ?>').submit()">Delete note</button>
                        <button type="button" data-note-action="duplicate" data-note-id="<?= e($note['id']) ?>">Duplicate</button>
                        <button type="button" data-copy-note="<?= e($copyText) ?>">Copy note</button>
                        <button type="button" data-note-action="labels" data-note-id="<?= e($note['id']) ?>">Add labels</button>
                        <a href="<?= e(url('notes/show?id=' . $note['id'])) ?>">Note details</a>
                        <button type="button" data-note-action="share" data-note-id="<?= e($note['id']) ?>">Share note</button>
                    </div>
                </div>
            </div>
            <p class="keep-status" data-card-status></p>
        </article>
        <?php
    }
}
?>

<section class="panel">
    <form class="filter-grid" method="get" action="<?= e(url('archived')) ?>" data-notes-search-form>
        <label class="search-field">Search
            <span class="search-input-wrap">
                <span class="search-icon" aria-hidden="true">&#128269;</span>
                <input type="search" name="search" value="<?= e($filters['search']) ?>" placeholder="Search archived notes..." autocomplete="off">
            </span>
        </label>
        <label>Category
            <select name="category_id">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['id']) ?>" <?= (string)$filters['category_id'] === (string)$category['id'] ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Tag
            <select name="tag_id">
                <option value="">All tags</option>
                <?php foreach ($tags as $tag): ?>
                    <option value="<?= e($tag['id']) ?>" <?= (string)$filters['tag_id'] === (string)$tag['id'] ? 'selected' : '' ?>>
                        <?= e($tag['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Priority
            <select name="priority">
                <option value="">Any</option>
                <option value="Normal" <?= $filters['priority'] === 'Normal' ? 'selected' : '' ?>>Normal</option>
                <option value="Important" <?= $filters['priority'] === 'Important' ? 'selected' : '' ?>>Important</option>
            </select>
        </label>
        <label>Pinned
            <select name="pinned">
                <option value="">Any</option>
                <option value="1" <?= $filters['pinned'] === '1' ? 'selected' : '' ?>>Pinned</option>
                <option value="0" <?= $filters['pinned'] === '0' ? 'selected' : '' ?>>Unpinned</option>
            </select>
        </label>
        <div class="filter-actions">
            <button class="button button-primary" type="submit">Search</button>
            <a class="button button-muted" href="<?= e(url('archived')) ?>">Reset</a>
        </div>
    </form>
</section>

<div class="notes-grid keep-grid" data-notes-search-results>
    <?php foreach ($notes as $note): ?>
        <?php renderKeepCard($note); ?>
    <?php endforeach; ?>
    <?php if (!$notes): ?>
        <div class="empty-state wide"><?= $filters['search'] !== '' ? 'No archived notes found.' : 'Archive is empty.' ?></div>
    <?php endif; ?>
</div>

<!-- Edit Note Modal (for Read-Only display on Archived Page) -->
<div class="keep-modal-backdrop" id="keepEditModalBackdrop">
    <div class="keep-modal" id="keepEditModal" data-note-id="">
        <div class="keep-modal-image-wrap" id="keepModalImageWrap" hidden>
            <img src="" id="keepModalImage" alt="Note image">
        </div>
        <div class="keep-modal-header" style="pointer-events: none;">
            <input type="text" id="keepModalTitle" placeholder="Title" autocomplete="off" readonly>
        </div>
        <div class="keep-modal-body" style="pointer-events: none;">
            <textarea id="keepModalContent" placeholder="Note content..." autocomplete="off" readonly></textarea>
            <div id="keepModalTagsRow" class="keep-tags"></div>
        </div>
        <div class="keep-modal-footer">
            <div class="keep-actionbar">
                <button class="keep-icon-btn archive-btn" type="button" title="Unarchive" id="keepModalArchiveBtn">📤</button>
                <button class="keep-icon-btn" type="button" title="Delete" id="keepModalDeleteBtn">🗑</button>
            </div>
            <button class="keep-close-btn" id="keepModalCloseBtn" type="button">Close</button>
        </div>
    </div>
</div>
