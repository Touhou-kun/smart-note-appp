<section class="panel">
    <form class="filter-grid" method="get" action="<?= e(url($isRecycleBin ? 'recycle-bin' : 'notes')) ?>" data-notes-search-form>
        <label class="search-field">Search
            <span class="search-input-wrap">
                <span class="search-icon" aria-hidden="true">&#128269;</span>
                <input type="search" name="search" value="<?= e($filters['search']) ?>" placeholder="Search notes..." autocomplete="off">
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
            <a class="button button-muted" href="<?= e(url($isRecycleBin ? 'recycle-bin' : 'notes')) ?>">Reset</a>
            <?php if (!$isRecycleBin): ?>
                <a class="button button-primary" href="<?= e(url('notes/create')) ?>">New Note</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="notes-grid" data-notes-search-results>
    <?php foreach ($notes as $note): ?>
        <?php $selectedTagIds = array_map('intval', array_column($note['tags'] ?? [], 'id')); ?>
        <article class="note-card <?= $note['is_pinned'] ? 'is-pinned' : '' ?>" data-editable-note data-note-id="<?= e($note['id']) ?>">
            <div class="note-read-view" data-note-read-view>
                <?php if ($note['image_path']): ?>
                    <img class="note-thumb" src="<?= e(upload_url($note['image_path'])) ?>" alt="<?= e($note['title']) ?>">
                <?php endif; ?>
                <div class="note-card-body">
                    <div class="note-meta">
                        <span class="badge"><?= e($note['priority']) ?></span>
                        <?php if ($note['is_pinned']): ?><span class="badge badge-pin">Pinned</span><?php endif; ?>
                        <?php if (!empty($note['is_favorite'])): ?><span class="badge">Favorite</span><?php endif; ?>
                    </div>
                    <h2><?= e($note['title']) ?></h2>
                    <p><?= e(strlen($note['content']) > 140 ? substr($note['content'], 0, 140) . '...' : $note['content']) ?></p>
                    <div class="tag-row">
                        <span><?= e($note['category_name'] ?? 'Uncategorized') ?></span>
                        <?php foreach ($note['tags'] as $tag): ?>
                            <span>#<?= e($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if (!$isRecycleBin): ?>
                <form class="note-inline-form" enctype="multipart/form-data" data-note-edit-form hidden>
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($note['id']) ?>">

                    <label>Title
                        <input type="text" name="title" required maxlength="180" value="<?= e($note['title']) ?>">
                    </label>

                    <label>Content
                        <textarea name="content" rows="7" required><?= e($note['content']) ?></textarea>
                    </label>

                    <div class="form-grid two">
                        <label>Category
                            <select name="category_id">
                                <option value="">Uncategorized</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e($category['id']) ?>" <?= (string)($note['category_id'] ?? '') === (string)$category['id'] ? 'selected' : '' ?>>
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Priority
                            <select name="priority">
                                <option value="Normal" <?= $note['priority'] === 'Normal' ? 'selected' : '' ?>>Normal</option>
                                <option value="Important" <?= $note['priority'] === 'Important' ? 'selected' : '' ?>>Important</option>
                            </select>
                        </label>
                    </div>

                    <fieldset class="checkbox-group">
                        <legend>Tags</legend>
                        <?php foreach ($tags as $tag): ?>
                            <label class="check-pill">
                                <input type="checkbox" name="tags[]" value="<?= e($tag['id']) ?>" <?= in_array((int)$tag['id'], $selectedTagIds, true) ? 'checked' : '' ?>>
                                <span><?= e($tag['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                        <?php if (!$tags): ?><p class="muted">Create tags first to attach them to notes.</p><?php endif; ?>
                    </fieldset>

                    <label>Image Attachment
                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    </label>

                    <div class="inline-status" data-note-edit-status></div>
                    <div class="form-actions">
                        <button class="button button-primary" type="submit">Save</button>
                        <button class="button button-muted" type="button" data-note-cancel-edit>Cancel</button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="card-actions">
                <?php if (!$isRecycleBin): ?>
                    <button class="button button-muted" type="button" data-note-action="favorite" data-note-id="<?= e($note['id']) ?>">
                        <?= !empty($note['is_favorite']) ? 'Unfavorite' : 'Favorite' ?>
                    </button>
                    <form method="post" action="<?= e(url('notes/toggle-pin')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($note['id']) ?>">
                        <input type="hidden" name="return_to" value="notes">
                        <button class="button button-muted" type="submit"><?= $note['is_pinned'] ? 'Unpin' : 'Pin' ?></button>
                    </form>
                    <button class="button button-muted" type="button" data-note-action="archive" data-note-id="<?= e($note['id']) ?>">Archive</button>
                    <a class="button button-muted" href="<?= e(url('notes/edit?id=' . $note['id'])) ?>">Edit Full</a>
                    <form method="post" action="<?= e(url('notes/delete')) ?>" data-confirm="Move this note to the recycle bin?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($note['id']) ?>">
                        <button class="button button-danger" type="submit">Delete</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= e(url('notes/restore')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($note['id']) ?>">
                        <button class="button button-primary" type="submit">Restore</button>
                    </form>
                    <form method="post" action="<?= e(url('notes/force-delete')) ?>" data-confirm="Permanently delete this note and image?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($note['id']) ?>">
                        <button class="button button-danger" type="submit">Delete Forever</button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$notes): ?>
        <div class="empty-state wide"><?= $filters['search'] !== '' ? 'No notes found.' : ($isRecycleBin ? 'Recycle bin is empty.' : 'No notes matched your criteria.') ?></div>
    <?php endif; ?>
</section>
