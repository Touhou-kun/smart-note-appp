<?php

declare(strict_types=1);

class TagController extends Controller
{
    private Tag $tags;

    public function __construct()
    {
        $this->tags = new Tag();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('tags/index', [
            'title' => 'Tags',
            'tags' => $this->tags->all(current_user_id()),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        verify_csrf();
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            flash('error', 'Tag name is required.');
            redirect('tags');
        }

        try {
            $this->tags->create(current_user_id(), $name);
            flash('success', 'Tag created.');
        } catch (PDOException) {
            flash('error', 'This tag already exists.');
        }
        redirect('tags');
    }

    public function update(): void
    {
        $this->requireAuth();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id <= 0 || $name === '') {
            flash('error', 'Valid tag details are required.');
            redirect('tags');
        }

        try {
            $this->tags->update($id, current_user_id(), $name);
            flash('success', 'Tag updated.');
        } catch (PDOException) {
            flash('error', 'This tag name is already in use.');
        }
        redirect('tags');
    }

    public function delete(): void
    {
        $this->requireAuth();
        verify_csrf();
        $this->tags->delete((int)($_POST['id'] ?? 0), current_user_id());
        flash('success', 'Tag deleted.');
        redirect('tags');
    }
}

