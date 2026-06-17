<article class="shared-note">
    <div class="form-heading">
        <p class="eyebrow">Shared Note</p>
        <h2><?= e($note['title']) ?></h2>
        <p><?= e($note['category_name'] ?? 'Uncategorized') ?> - Shared by <?= e($note['owner_name'] ?? APP_NAME) ?></p>
    </div>

    <div class="note-meta">
        <span class="badge"><?= e($note['priority']) ?></span>
        <?php if ($note['is_pinned']): ?><span class="badge badge-pin">Pinned</span><?php endif; ?>
        <?php if (!empty($note['is_favorite'])): ?><span class="badge">Favorite</span><?php endif; ?>
    </div>

    <?php if ($note['image_path']): ?>
        <img class="detail-image" src="<?= e(upload_url($note['image_path'])) ?>" alt="<?= e($note['title']) ?>">
    <?php endif; ?>

    <div class="note-content"><?= nl2br(e($note['content'])) ?></div>

    <div class="tag-row">
        <?php foreach ($note['tags'] as $tag): ?>
            <span>#<?= e($tag['name']) ?></span>
        <?php endforeach; ?>
    </div>
</article>
