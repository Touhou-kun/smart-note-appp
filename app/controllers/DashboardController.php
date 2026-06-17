<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $userId = current_user_id();
        $noteCounts = (new Note())->counts($userId);

        $stats = [
            ['label' => 'Total Notes', 'value' => $noteCounts['notes'], 'tone' => 'blue'],
            ['label' => 'Total Categories', 'value' => (new Category())->count($userId), 'tone' => 'green'],
            ['label' => 'Total Tags', 'value' => (new Tag())->count($userId), 'tone' => 'violet'],
            ['label' => 'Important Notes', 'value' => $noteCounts['important'], 'tone' => 'amber'],
            ['label' => 'Pinned Notes', 'value' => $noteCounts['pinned'], 'tone' => 'rose'],
            ['label' => 'Deleted Notes', 'value' => $noteCounts['deleted'], 'tone' => 'slate'],
        ];

        $recentNotes = (new Note())->all($userId, ['deleted' => '0', 'archived' => '0']);
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentNotes' => $recentNotes,
        ]);
    }
}

