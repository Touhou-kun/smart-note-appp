<section class="stats-grid">
    <?php foreach ($stats as $stat): ?>
        <article class="stat-card tone-<?= e($stat['tone']) ?>">
            <p><?= e($stat['label']) ?></p>
            <strong><?= e($stat['value']) ?></strong>
        </article>
    <?php endforeach; ?>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <h2>Recent Notes</h2>
            <p>Your newest active notes, with pinned notes first.</p>
        </div>
        <a class="button button-primary" href="<?= e(url('notes/create')) ?>">New Note</a>
    </div>
    <?php if (!$recentNotes): ?>
        <div class="empty-state">No notes yet. Create your first note to start organizing.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Pinned</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentNotes as $note): ?>
                        <tr>
                            <td><a href="<?= e(url('notes/show?id=' . $note['id'])) ?>"><?= e($note['title']) ?></a></td>
                            <td><?= e($note['category_name'] ?? 'Uncategorized') ?></td>
                            <td><span class="badge"><?= e($note['priority']) ?></span></td>
                            <td><?= $note['is_pinned'] ? 'Yes' : 'No' ?></td>
                            <td><?= e($note['updated_at'] ?? $note['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

