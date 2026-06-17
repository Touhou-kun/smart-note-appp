<?php

declare(strict_types=1);

require __DIR__ . '/config/config.php';

spl_autoload_register(function (string $class): void {
    foreach (['app/core', 'app/controllers', 'app/models'] as $directory) {
        $file = BASE_PATH . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

$router = new Router();

$router->get('/', [DashboardController::class, 'index']);
$router->get('dashboard', [DashboardController::class, 'index']);

$router->get('register', [AuthController::class, 'register']);
$router->post('register', [AuthController::class, 'store']);
$router->get('login', [AuthController::class, 'login']);
$router->post('login', [AuthController::class, 'authenticate']);
$router->post('logout', [AuthController::class, 'logout']);

$router->get('notes', [NoteController::class, 'index']);
$router->get('notes/create', [NoteController::class, 'create']);
$router->post('notes/create', [NoteController::class, 'store']);
$router->get('notes/edit', [NoteController::class, 'edit']);
$router->post('notes/edit', [NoteController::class, 'update']);
$router->get('notes/show', [NoteController::class, 'show']);
$router->post('notes/delete', [NoteController::class, 'delete']);
$router->post('notes/restore', [NoteController::class, 'restore']);
$router->post('notes/force-delete', [NoteController::class, 'forceDelete']);
$router->post('notes/toggle-pin', [NoteController::class, 'togglePin']);
$router->post('api/notes/autosave', [NoteController::class, 'autoSave']);
$router->post('api/notes/update', [NoteController::class, 'updateApi']);
$router->post('api/notes/toggle-pin', [NoteController::class, 'togglePinApi']);
$router->post('api/notes/toggle-favorite', [NoteController::class, 'toggleFavoriteApi']);
$router->post('api/notes/archive', [NoteController::class, 'archiveApi']);
$router->post('api/notes/delete', [NoteController::class, 'deleteApi']);
$router->get('api/notes', [NoteController::class, 'dashboardApi']);
$router->get('recycle-bin', [NoteController::class, 'recycleBin']);

$router->get('categories', [CategoryController::class, 'index']);
$router->post('categories/create', [CategoryController::class, 'store']);
$router->post('categories/update', [CategoryController::class, 'update']);
$router->post('categories/delete', [CategoryController::class, 'delete']);

$router->get('tags', [TagController::class, 'index']);
$router->post('tags/create', [TagController::class, 'store']);
$router->post('tags/update', [TagController::class, 'update']);
$router->post('tags/delete', [TagController::class, 'delete']);

$router->get('profile', [ProfileController::class, 'index']);
$router->post('profile/password', [ProfileController::class, 'changePassword']);

$router->dispatch();

