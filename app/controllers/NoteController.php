<?php

declare(strict_types=1);

class NoteController extends Controller
{
    private Note $notes;
    private Category $categories;
    private Tag $tags;
    private bool $uploadFailed = false;

    public function __construct()
    {
        $this->notes = new Note();
        $this->categories = new Category();
        $this->tags = new Tag();
    }

    public function index(): void
    {
        $this->requireAuth();
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'category_id' => $_GET['category_id'] ?? '',
            'tag_id' => $_GET['tag_id'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'pinned' => $_GET['pinned'] ?? '',
            'deleted' => '0',
            'archived' => '0',
        ];

        $this->view('notes/index', [
            'title' => 'Notes',
            'notes' => $this->notes->all(current_user_id(), $filters),
            'categories' => $this->categories->all(current_user_id()),
            'tags' => $this->tags->all(current_user_id()),
            'filters' => $filters,
            'isRecycleBin' => false,
        ]);
    }

    public function recycleBin(): void
    {
        $this->requireAuth();
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'category_id' => $_GET['category_id'] ?? '',
            'tag_id' => $_GET['tag_id'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'pinned' => $_GET['pinned'] ?? '',
            'deleted' => '1',
        ];

        $this->view('notes/index', [
            'title' => 'Recycle Bin',
            'notes' => $this->notes->all(current_user_id(), $filters),
            'categories' => $this->categories->all(current_user_id()),
            'tags' => $this->tags->all(current_user_id()),
            'filters' => $filters,
            'isRecycleBin' => true,
        ]);
    }

    public function archived(): void
    {
        $this->requireAuth();
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'category_id' => $_GET['category_id'] ?? '',
            'tag_id' => $_GET['tag_id'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'pinned' => $_GET['pinned'] ?? '',
            'deleted' => '0',
            'archived' => '1',
        ];

        $this->view('notes/archived', [
            'title' => 'Archive',
            'notes' => $this->notes->all(current_user_id(), $filters),
            'categories' => $this->categories->all(current_user_id()),
            'tags' => $this->tags->all(current_user_id()),
            'filters' => $filters,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('notes/form', [
            'title' => 'Create Note',
            'note' => null,
            'categories' => $this->categories->all(current_user_id()),
            'tags' => $this->tags->all(current_user_id()),
            'selectedTags' => [],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        verify_csrf();
        $data = $this->validatedNoteData();
        if (!$data) {
            redirect('notes/create');
        }

        $data['image_path'] = $this->handleUpload();
        if ($this->uploadFailed) {
            redirect('notes/create');
        }

        $this->notes->create(current_user_id(), $data, $_POST['tags'] ?? []);
        flash('success', 'Note created.');
        redirect('notes');
    }

    public function edit(): void
    {
        $this->requireAuth();
        $note = $this->notes->find((int)($_GET['id'] ?? 0), current_user_id());
        if (!$note) {
            flash('error', 'Note not found.');
            redirect('notes');
        }

        $this->view('notes/form', [
            'title' => 'Edit Note',
            'note' => $note,
            'categories' => $this->categories->all(current_user_id()),
            'tags' => $this->tags->all(current_user_id()),
            'selectedTags' => array_column($note['tags'], 'id'),
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $note = $this->notes->find($id, current_user_id());

        if (!$note) {
            flash('error', 'Note not found.');
            redirect('notes');
        }

        $data = $this->validatedNoteData();
        if (!$data) {
            redirect('notes/edit?id=' . $id);
        }

        $data['image_path'] = $note['image_path'];

        if (!empty($_FILES['image']['name'])) {
            $newImage = $this->handleUpload();
            if ($this->uploadFailed) {
                redirect('notes/edit?id=' . $id);
            }

            if ($newImage !== null) {
                $this->deleteImage($note['image_path']);
                $data['image_path'] = $newImage;
            }
        }

        $this->notes->update($id, current_user_id(), $data, $_POST['tags'] ?? []);
        flash('success', 'Note updated.');
        redirect('notes/show?id=' . $id);
    }

    public function show(): void
    {
        $this->requireAuth();
        $note = $this->notes->find((int)($_GET['id'] ?? 0), current_user_id());
        if (!$note) {
            flash('error', 'Note not found.');
            redirect('notes');
        }
        $this->view('notes/show', ['title' => $note['title'], 'note' => $note]);
    }

    public function delete(): void
    {
        $this->requireAuth();
        verify_csrf();
        $this->notes->softDelete((int)($_POST['id'] ?? 0), current_user_id());
        flash('success', 'Note moved to recycle bin.');
        redirect('notes');
    }

    public function restore(): void
    {
        $this->requireAuth();
        verify_csrf();
        $this->notes->restore((int)($_POST['id'] ?? 0), current_user_id());
        flash('success', 'Note restored.');
        redirect('recycle-bin');
    }

    public function forceDelete(): void
    {
        $this->requireAuth();
        verify_csrf();
        $note = $this->notes->find((int)($_POST['id'] ?? 0), current_user_id());
        if ($note) {
            $this->deleteImage($note['image_path']);
            $this->notes->forceDelete((int)$note['id'], current_user_id());
        }
        flash('success', 'Note permanently deleted.');
        redirect('recycle-bin');
    }

    public function togglePin(): void
    {
        $this->requireAuth();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $this->notes->togglePin($id, current_user_id());
        flash('success', 'Pin status updated.');
        redirect($_POST['return_to'] ?? 'notes');
    }

    private function validatedNoteData(): array
    {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $priority = $_POST['priority'] ?? 'Normal';

        if ($title === '' || $content === '') {
            flash('error', 'Title and content are required.');
            return [];
        }

        if (!in_array($priority, ['Normal', 'Important'], true)) {
            flash('error', 'Invalid priority selected.');
            return [];
        }

        return [
            'title' => $title,
            'content' => $content,
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'priority' => $priority,
            'is_pinned' => isset($_POST['is_pinned']) ? 1 : 0,
            'image_path' => null,
        ];
    }

    private function handleUpload(): ?string
    {
        if (empty($_FILES['image']['name'])) {
            return null;
        }

        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadFailed = true;
            flash('error', 'Image upload failed.');
            return null;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->uploadFailed = true;
            flash('error', 'Image must be 5MB or smaller.');
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            $this->uploadFailed = true;
            flash('error', 'Only JPG, PNG, and WEBP images are allowed.');
            return null;
        }

        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $destination = UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->uploadFailed = true;
            flash('error', 'Unable to save uploaded image.');
            return null;
        }

        return $filename;
    }

    private function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $file = UPLOAD_PATH . DIRECTORY_SEPARATOR . basename($path);
        if (is_file($file)) {
            unlink($file);
        }
    }

    // API Methods for Dashboard
    public function dashboardApi(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');
        $notes = $this->notes->all(current_user_id(), [
            'search' => trim($_GET['search'] ?? ''),
            'deleted' => '0',
            'archived' => '0',
        ]);
        echo json_encode($notes);
    }

    public function updateApi(): void
    {
        $this->requireAuth();
        verify_csrf();
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 0);
        $note = $this->notes->find($id, current_user_id());
        if (!$note) {
            http_response_code(404);
            echo json_encode(['error' => 'Note not found']);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $priority = $_POST['priority'] ?? 'Normal';

        if ($title === '' || $content === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Title and content are required.']);
            return;
        }

        if (!in_array($priority, ['Normal', 'Important'], true)) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid priority selected.']);
            return;
        }

        $imagePath = $note['image_path'];
        if (!empty($_FILES['image']['name'])) {
            $newImage = $this->handleUpload();
            if ($this->uploadFailed) {
                http_response_code(422);
                echo json_encode(['error' => 'Image upload failed.']);
                return;
            }

            if ($newImage !== null) {
                $this->deleteImage($note['image_path']);
                $imagePath = $newImage;
            }
        }

        $updated = $this->notes->updateDetails($id, current_user_id(), [
            'title' => $title,
            'content' => $content,
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'priority' => $priority,
            'image_path' => $imagePath,
        ], $_POST['tags'] ?? []);

        echo json_encode(['success' => true, 'note' => $updated]);
    }

    public function autoSave(): void
    {
        $this->requireAuth();
        verify_csrf();
        header('Content-Type: application/json');
        
        $id = (int)($_POST['id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $title = trim($_POST['title'] ?? '');

        $note = $this->notes->find($id, current_user_id());
        if (!$note) {
            http_response_code(404);
            echo json_encode(['error' => 'Note not found']);
            return;
        }

        $this->notes->update($id, current_user_id(), [
            'title' => $title ?: $note['title'],
            'content' => $content,
            'category_id' => $note['category_id'],
            'priority' => $note['priority'],
            'is_pinned' => $note['is_pinned'],
            'image_path' => $note['image_path'],
        ], array_column($note['tags'] ?? [], 'id'));

        echo json_encode(['success' => true, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function togglePinApi(): void
    {
        $this->requireAuth();
        verify_csrf();
        header('Content-Type: application/json');
        
        $id = (int)($_POST['id'] ?? 0);
        $note = $this->notes->find($id, current_user_id());

        if (!$note) {
            http_response_code(404);
            echo json_encode(['error' => 'Note not found']);
            return;
        }

        $this->notes->togglePin($id, current_user_id());
        $updated = $this->notes->find($id, current_user_id());
        
        echo json_encode(['success' => true, 'is_pinned' => (int)$updated['is_pinned']]);
    }

    public function toggleFavoriteApi(): void
    {
        $this->requireAuth();
        verify_csrf();
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 0);
        $note = $this->notes->find($id, current_user_id());

        if (!$note) {
            http_response_code(404);
            echo json_encode(['error' => 'Note not found']);
            return;
        }

        $updated = $this->notes->toggleFavorite($id, current_user_id());
        if (!$updated) {
            http_response_code(422);
            echo json_encode(['error' => 'Favorite is not available for this database.']);
            return;
        }

        echo json_encode(['success' => true, 'is_favorite' => (int)($updated['is_favorite'] ?? 0)]);
    }

    public function archiveApi(): void
    {
        $this->requireAuth();
        verify_csrf();
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 0);
        $note = $this->notes->find($id, current_user_id());

        if (!$note) {
            http_response_code(404);
            echo json_encode(['error' => 'Note not found']);
            return;
        }

        $updated = $this->notes->archive($id, current_user_id());
        if (!$updated) {
            http_response_code(422);
            echo json_encode(['error' => 'Archive is not available for this database.']);
            return;
        }

        echo json_encode(['success' => true, 'is_archived' => (int)($updated['is_archived'] ?? 0)]);
    }

    public function deleteApi(): void
    {
        $this->requireAuth();
        verify_csrf();
        header('Content-Type: application/json');
        
        $id = (int)($_POST['id'] ?? 0);
        $note = $this->notes->find($id, current_user_id());

        if (!$note) {
            http_response_code(404);
            echo json_encode(['error' => 'Note not found']);
            return;
        }

        $this->notes->softDelete($id, current_user_id());
        echo json_encode(['success' => true, 'message' => 'Note moved to recycle bin']);
    }
}
