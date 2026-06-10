<?php

declare(strict_types=1);

class CategoryController extends Controller
{
    private Category $categories;

    public function __construct()
    {
        $this->categories = new Category();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('categories/index', [
            'title' => 'Categories',
            'categories' => $this->categories->all(current_user_id()),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        verify_csrf();
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            flash('error', 'Category name is required.');
            redirect('categories');
        }

        try {
            $this->categories->create(current_user_id(), $name);
            flash('success', 'Category created.');
        } catch (PDOException) {
            flash('error', 'This category already exists.');
        }
        redirect('categories');
    }

    public function update(): void
    {
        $this->requireAuth();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id <= 0 || $name === '') {
            flash('error', 'Valid category details are required.');
            redirect('categories');
        }

        try {
            $this->categories->update($id, current_user_id(), $name);
            flash('success', 'Category updated.');
        } catch (PDOException) {
            flash('error', 'This category name is already in use.');
        }
        redirect('categories');
    }

    public function delete(): void
    {
        $this->requireAuth();
        verify_csrf();
        $this->categories->delete((int)($_POST['id'] ?? 0), current_user_id());
        flash('success', 'Category deleted. Existing notes were uncategorized.');
        redirect('categories');
    }
}

