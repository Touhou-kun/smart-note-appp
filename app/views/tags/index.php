<section class="panel split-panel">
    <form class="inline-form" method="post" action="<?= e(url('tags/create')) ?>">
        <?= csrf_field() ?>
        <h2>Create Tag</h2>
        <label>Name
            <input type="text" name="name" required maxlength="120" placeholder="Exam">
        </label>
        <button class="button button-primary" type="submit">Create</button>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Tags</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Created</th>
                    <th class="actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tags as $tag): ?>
                    <tr>
                        <td>
                            <form class="row-form" method="post" action="<?= e(url('tags/update')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($tag['id']) ?>">
                                <input type="text" name="name" value="<?= e($tag['name']) ?>" required maxlength="120">
                                <button class="button button-muted" type="submit">Save</button>
                            </form>
                        </td>
                        <td><?= e($tag['created_at']) ?></td>
                        <td>
                            <form method="post" action="<?= e(url('tags/delete')) ?>" data-confirm="Delete this tag?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($tag['id']) ?>">
                                <button class="button button-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$tags): ?>
                    <tr><td colspan="3" class="empty-state">No tags created yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

