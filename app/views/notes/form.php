<?php
$isEdit = $note !== null;
$action = $isEdit ? url('notes/edit') : url('notes/create');
?>
<section class="panel">
    <form class="note-form" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e($note['id']) ?>">
        <?php endif; ?>

        <div class="form-grid two">
            <label>Title
                <input type="text" name="title" required maxlength="180" value="<?= e($note['title'] ?? '') ?>">
            </label>
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
        </div>

        <label>Content
            <textarea name="content" rows="12" required><?= e($note['content'] ?? '') ?></textarea>
        </label>

        <div class="form-grid two">
            <label>Priority
                <select name="priority">
                    <option value="Normal" <?= ($note['priority'] ?? 'Normal') === 'Normal' ? 'selected' : '' ?>>Normal</option>
                    <option value="Important" <?= ($note['priority'] ?? '') === 'Important' ? 'selected' : '' ?>>Important</option>
                </select>
            </label>
            <label>Image Attachment
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-image-input>
            </label>
        </div>

        <fieldset class="checkbox-group">
            <legend>Tags</legend>
            <?php foreach ($tags as $tag): ?>
                <label class="check-pill">
                    <input type="checkbox" name="tags[]" value="<?= e($tag['id']) ?>" <?= in_array($tag['id'], $selectedTags, true) ? 'checked' : '' ?>>
                    <span><?= e($tag['name']) ?></span>
                </label>
            <?php endforeach; ?>
            <?php if (!$tags): ?><p class="muted">Create tags first to attach them to notes.</p><?php endif; ?>
        </fieldset>

        <label class="check-line">
            <input type="checkbox" name="is_pinned" value="1" <?= !empty($note['is_pinned']) ? 'checked' : '' ?>>
            Keep this note pinned
        </label>

        <div class="preview-block">
            <?php if ($isEdit && $note['image_path']): ?>
                <img src="<?= e(upload_url($note['image_path'])) ?>" alt="Current image" data-image-preview>
            <?php else: ?>
                <img src="" alt="Selected image preview" data-image-preview hidden>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button class="button button-primary" type="submit"><?= $isEdit ? 'Update Note' : 'Create Note' ?></button>
            <a class="button button-muted" href="<?= e(url('notes')) ?>">Cancel</a>
        </div>
    </form>
</section>

