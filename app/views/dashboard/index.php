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
            <h2>Your Notes</h2>
            <p>Organize and manage all your notes with ease</p>
        </div>
        <a class="button button-primary" href="<?= e(url('notes/create')) ?>">+ New Note</a>
    </div>

    <?php if (!$recentNotes): ?>
        <div class="empty-state">
            <div class="empty-state-icon">SN</div>
            <p>No notes yet. <a href="<?= e(url('notes/create')) ?>">Create your first note</a> to start organizing.</p>
        </div>
    <?php else: ?>
        <div class="notes-container" id="notesContainer" data-dashboard-search-results>
            <?php
            $pinnedNotes = array_filter($recentNotes, fn($n) => $n['is_pinned']);
            $unpinnedNotes = array_filter($recentNotes, fn($n) => !$n['is_pinned']);
            $sortedNotes = array_merge($pinnedNotes, $unpinnedNotes);
            ?>
            <?php foreach ($sortedNotes as $note): ?>
                <?php
                $tagNames = array_map(static fn(array $tag): string => $tag['name'], $note['tags'] ?? []);
                $searchText = implode(' ', [
                    $note['title'],
                    $note['content'],
                    $note['category_name'] ?? '',
                    implode(' ', $tagNames),
                ]);
                ?>
                <div class="note-card <?= $note['is_pinned'] ? 'is-pinned' : '' ?>" data-note-id="<?= e($note['id']) ?>" data-search-text="<?= e($searchText) ?>" draggable="true">
                    <div class="note-card-header">
                        <h3 class="note-title"><?= e($note['title']) ?></h3>
                        <div class="quick-actions">
                            <button class="note-favorite-btn" data-note-id="<?= e($note['id']) ?>" title="<?= !empty($note['is_favorite']) ? 'Unfavorite' : 'Favorite' ?>">
                                <span class="favorite-icon"><?= !empty($note['is_favorite']) ? '&#9733;' : '&#9734;' ?></span>
                            </button>
                            <button class="note-pin-btn" data-note-id="<?= e($note['id']) ?>" title="<?= $note['is_pinned'] ? 'Unpin' : 'Pin' ?>">
                                <span class="pin-icon"><?= $note['is_pinned'] ? '&#9679;' : '&#9675;' ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="note-content"><?= e(strlen($note['content']) > 180 ? substr($note['content'], 0, 180) . '...' : $note['content']) ?></div>

                    <?php if ($note['image_path']): ?>
                        <div class="note-image">
                            <img src="<?= e(upload_url($note['image_path'])) ?>" alt="Note image">
                        </div>
                    <?php endif; ?>

                    <div class="tag-row">
                        <?php foreach ($note['tags'] ?? [] as $tag): ?>
                            <span>#<?= e($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="note-meta">
                        <span class="note-category"><?= e($note['category_name'] ?? 'Uncategorized') ?></span>
                        <span class="note-priority <?= strtolower($note['priority']) ?>"><?= e($note['priority']) ?></span>
                        <span class="note-date"><?= date('M d, Y', strtotime($note['updated_at'] ?? $note['created_at'])) ?></span>
                    </div>

                    <div class="note-actions">
                        <a href="<?= e(url('notes/show?id=' . $note['id'])) ?>" class="action-btn" title="View">View</a>
                        <button class="action-btn archive-btn" data-note-id="<?= e($note['id']) ?>" title="Archive">Archive</button>
                        <button class="action-btn delete-btn" data-note-id="<?= e($note['id']) ?>" title="Delete">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
let draggedElement = null;

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('notesContainer');
    if (!container) return;

    container.addEventListener('dragstart', handleDragStart);
    container.addEventListener('dragover', handleDragOver);
    container.addEventListener('drop', handleDrop);
    container.addEventListener('dragend', handleDragEnd);

    document.querySelectorAll('.note-pin-btn').forEach(btn => {
        btn.addEventListener('click', togglePin);
    });

    document.querySelectorAll('.note-favorite-btn').forEach(btn => {
        btn.addEventListener('click', toggleFavorite);
    });

    document.querySelectorAll('.archive-btn').forEach(btn => {
        btn.addEventListener('click', archiveNote);
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', deleteNote);
    });
});

function handleDragStart(e) {
    if (e.target.classList.contains('note-card')) {
        draggedElement = e.target;
        e.target.style.opacity = '0.5';
        e.dataTransfer.effectAllowed = 'move';
    }
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';

    const afterElement = getDragAfterElement(e.clientY);
    const container = document.getElementById('notesContainer');

    if (afterElement == null) {
        container.appendChild(draggedElement);
    } else {
        container.insertBefore(draggedElement, afterElement);
    }
}

function handleDragEnd(e) {
    if (e.target.classList.contains('note-card')) {
        e.target.style.opacity = '1';
    }
}

function handleDrop(e) {
    e.preventDefault();
}

function getDragAfterElement(y) {
    const container = document.getElementById('notesContainer');
    const draggableElements = [...container.querySelectorAll('.note-card')];

    return draggableElements.reduce((closest, child) => {
        if (child === draggedElement) return closest;

        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        }

        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function postNoteAction(url, noteId) {
    const formData = new FormData();
    formData.append('id', noteId);
    formData.append('csrf_token', getCsrfToken());

    return fetch(url, {
        method: 'POST',
        body: formData
    }).then(response => response.json());
}

function togglePin(e) {
    e.preventDefault();
    const noteId = this.dataset.noteId;
    const indicator = this.querySelector('.pin-icon');

    postNoteAction('<?= e(url('api/notes/toggle-pin')) ?>', noteId)
        .then(data => {
            if (data.success) {
                indicator.innerHTML = data.is_pinned ? '&#9679;' : '&#9675;';
                this.title = data.is_pinned ? 'Unpin' : 'Pin';
                this.closest('.note-card')?.classList.toggle('is-pinned', Boolean(data.is_pinned));
            }
        })
        .catch(error => console.error('Error:', error));
}

function toggleFavorite(e) {
    e.preventDefault();
    const noteId = this.dataset.noteId;
    const indicator = this.querySelector('.favorite-icon');

    postNoteAction('<?= e(url('api/notes/toggle-favorite')) ?>', noteId)
        .then(data => {
            if (data.success) {
                indicator.innerHTML = data.is_favorite ? '&#9733;' : '&#9734;';
                this.title = data.is_favorite ? 'Unfavorite' : 'Favorite';
            }
        })
        .catch(error => console.error('Error:', error));
}

function archiveNote(e) {
    e.preventDefault();
    const noteCard = this.closest('.note-card');

    postNoteAction('<?= e(url('api/notes/archive')) ?>', this.dataset.noteId)
        .then(data => {
            if (data.success) {
                noteCard?.remove();
            }
        })
        .catch(error => console.error('Error:', error));
}

function deleteNote(e) {
    e.preventDefault();
    if (!confirm('Are you sure you want to delete this note?')) return;

    const noteCard = this.closest('.note-card');

    postNoteAction('<?= e(url('api/notes/delete')) ?>', this.dataset.noteId)
        .then(data => {
            if (data.success) {
                noteCard?.remove();
            }
        })
        .catch(error => console.error('Error:', error));
}
</script>
