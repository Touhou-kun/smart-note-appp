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
            <div class="empty-state-icon">📝</div>
            <p>No notes yet. <a href="<?= e(url('notes/create')) ?>">Create your first note</a> to start organizing.</p>
        </div>
    <?php else: ?>
        <div class="notes-container" id="notesContainer">
            <?php 
            // Separate pinned and unpinned notes
            $pinnedNotes = array_filter($recentNotes, fn($n) => $n['is_pinned']);
            $unpinnedNotes = array_filter($recentNotes, fn($n) => !$n['is_pinned']);
            $sortedNotes = array_merge($pinnedNotes, $unpinnedNotes);
            ?>
            <?php foreach ($sortedNotes as $note): ?>
                <div class="note-card <?= $note['is_pinned'] ? 'is-pinned' : '' ?>" data-note-id="<?= e($note['id']) ?>" draggable="true">
                    <div class="note-card-header">
                        <h3 class="note-title" contenteditable="true" data-field="title"><?= e($note['title']) ?></h3>
                        <button class="note-pin-btn" data-note-id="<?= e($note['id']) ?>" title="<?= $note['is_pinned'] ? 'Unpin' : 'Pin' ?>">
                            <span class="pin-icon"><?= $note['is_pinned'] ? '📌' : '📍' ?></span>
                        </button>
                    </div>
                    
                    <div class="note-content" contenteditable="true" data-field="content"><?= e($note['content']) ?></div>
                    
                    <?php if ($note['image_path']): ?>
                        <div class="note-image">
                            <img src="<?= e(url('public/uploads/' . $note['image_path'])) ?>" alt="Note image">
                        </div>
                    <?php endif; ?>
                    
                    <div class="note-meta">
                        <span class="note-category"><?= e($note['category_name'] ?? 'Uncategorized') ?></span>
                        <span class="note-priority <?= strtolower($note['priority']) ?>"><?= e($note['priority']) ?></span>
                        <span class="note-date"><?= date('M d, Y', strtotime($note['updated_at'] ?? $note['created_at'])) ?></span>
                    </div>
                    
                    <div class="note-actions">
                        <a href="<?= e(url('notes/show?id=' . $note['id'])) ?>" class="action-btn" title="View">👁️</a>
                        <a href="<?= e(url('notes/edit?id=' . $note['id'])) ?>" class="action-btn" title="Edit">✏️</a>
                        <button class="action-btn delete-btn" data-note-id="<?= e($note['id']) ?>" title="Delete">🗑️</button>
                    </div>
                    
                    <div class="note-save-indicator">Saved</div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
const AUTOSAVE_DELAY = 1500;
let saveTimeout;
let draggedElement = null;

// Get CSRF token from meta tag
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// Initialize notes container
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('notesContainer');
    if (!container) return;

    // Drag and drop listeners
    container.addEventListener('dragstart', handleDragStart);
    container.addEventListener('dragover', handleDragOver);
    container.addEventListener('drop', handleDrop);
    container.addEventListener('dragend', handleDragEnd);

    // Pin button listeners
    document.querySelectorAll('.note-pin-btn').forEach(btn => {
        btn.addEventListener('click', togglePin);
    });

    // Delete button listeners
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', deleteNote);
    });

    // Autosave listeners
    document.querySelectorAll('[contenteditable="true"]').forEach(element => {
        element.addEventListener('blur', scheduleAutosave);
        element.addEventListener('input', function() {
            const noteCard = this.closest('.note-card');
            const indicator = noteCard.querySelector('.note-save-indicator');
            if (indicator) indicator.textContent = 'Saving...';
        });
    });
});

// Drag and Drop functions
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
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// Pin/Unpin function
function togglePin(e) {
    e.preventDefault();
    const noteId = this.dataset.noteId;
    const indicator = this.querySelector('.pin-icon');

    const formData = new FormData();
    formData.append('id', noteId);
    formData.append('csrf_token', getCsrfToken());

    fetch('<?= e(url('api/notes/toggle-pin')) ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_pinned) {
                indicator.textContent = '📌';
                this.parentElement.parentElement.style.order = '-1';
            } else {
                indicator.textContent = '📍';
                this.parentElement.parentElement.style.order = '0';
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

// Delete function
function deleteNote(e) {
    e.preventDefault();
    if (!confirm('Are you sure you want to delete this note?')) return;

    const noteId = this.dataset.noteId;
    const noteCard = document.querySelector(`[data-note-id="${noteId}"]`);

    const formData = new FormData();
    formData.append('id', noteId);
    formData.append('csrf_token', getCsrfToken());

    fetch('<?= e(url('api/notes/delete')) ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            noteCard.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => noteCard.remove(), 300);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Autosave function
function scheduleAutosave(e) {
    const noteCard = e.target.closest('.note-card');
    if (!noteCard) return;
    
    const noteId = noteCard.dataset.noteId;
    const title = noteCard.querySelector('[data-field="title"]').textContent;
    const content = noteCard.querySelector('[data-field="content"]').textContent;

    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        const formData = new FormData();
        formData.append('id', noteId);
        formData.append('title', title);
        formData.append('content', content);
        formData.append('csrf_token', getCsrfToken());

        fetch('<?= e(url('api/notes/autosave')) ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const indicator = noteCard.querySelector('.note-save-indicator');
                if (indicator) {
                    indicator.textContent = 'Saved';
                    indicator.style.opacity = '1';
                    setTimeout(() => {
                        indicator.style.opacity = '0.5';
                    }, 1000);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }, AUTOSAVE_DELAY);
}
</script>

