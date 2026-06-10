<section class="panel split-panel">
    <form class="inline-form" method="post" action="<?= e(url('categories/create')) ?>">
        <?= csrf_field() ?>
        <h2>Create Category</h2>
        <label>Name
            <input type="text" name="name" required maxlength="120" placeholder="Coursework">
        </label>
        <button class="button button-primary" type="submit">Create</button>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Categories</h2>
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
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <form class="row-form" method="post" action="<?= e(url('categories/update')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                                <input type="text" name="name" value="<?= e($category['name']) ?>" required maxlength="120">
                                <button class="button button-muted" type="submit">Save</button>
                            </form>
                        </td>
                        <td><?= e($category['created_at']) ?></td>
                        <td>
                            <form method="post" action="<?= e(url('categories/delete')) ?>" data-confirm="Delete this category? Notes will become uncategorized.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                                <button class="button button-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$categories): ?>
                    <tr><td colspan="3" class="empty-state">No categories created yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

