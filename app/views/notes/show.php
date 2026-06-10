<article class="panel note-detail">
    <div class="panel-header">
        <div>
            <div class="note-meta">
                <span class="badge"><?= e($note['priority']) ?></span>
                <?php if ($note['is_pinned']): ?><span class="badge badge-pin">Pinned</span><?php endif; ?>
                <?php if ($note['is_deleted']): ?><span class="badge badge-danger">Deleted</span><?php endif; ?>
            </div>
            <h2><?= e($note['title']) ?></h2>
            <p><?= e($note['category_name'] ?? 'Uncategorized') ?> · <?= e($note['updated_at'] ?? $note['created_at']) ?></p>
        </div>
        <div class="card-actions">
            <?php if (!$note['is_deleted']): ?>
                <a class="button button-muted" href="<?= e(url('notes/edit?id=' . $note['id'])) ?>">Edit</a>
                <form method="post" action="<?= e(url('notes/toggle-pin')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($note['id']) ?>">
                    <input type="hidden" name="return_to" value="notes/show?id=<?= e($note['id']) ?>">
                    <button class="button button-muted" type="submit"><?= $note['is_pinned'] ? 'Unpin' : 'Pin' ?></button>
                </form>
            <?php endif; ?>
        </div>
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

