<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(($title ?? APP_NAME) . ' - ' . APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="<?= e(url('dashboard')) ?>">
                <span class="brand-mark">SN</span>
                <span>Smart Note</span>
            </a>
            <nav class="side-nav">
                <a class="<?= e(active_route('dashboard')) ?>" href="<?= e(url('dashboard')) ?>">Dashboard</a>
                <a class="<?= e(active_route('notes')) ?>" href="<?= e(url('notes')) ?>">Notes</a>
                <a class="<?= e(active_route('archived')) ?>" href="<?= e(url('archived')) ?>">Archive</a>
                <a class="<?= e(active_route('categories')) ?>" href="<?= e(url('categories')) ?>">Categories</a>
                <a class="<?= e(active_route('tags')) ?>" href="<?= e(url('tags')) ?>">Tags</a>
                <a class="<?= e(active_route('recycle-bin')) ?>" href="<?= e(url('recycle-bin')) ?>">Recycle Bin</a>
                <a class="<?= e(active_route('profile')) ?>" href="<?= e(url('profile')) ?>">Profile</a>
            </nav>
        </aside>

        <div class="content-shell">
            <header class="topbar">
                <button class="icon-button menu-toggle" type="button" data-sidebar-toggle aria-label="Toggle menu">☰</button>
                <div>
                    <p class="eyebrow">Workspace</p>
                    <h1><?= e($title ?? APP_NAME) ?></h1>
                </div>
                <div class="top-actions">
                    <button class="icon-button" type="button" data-theme-toggle aria-label="Toggle dark mode">◐</button>
                    <form method="post" action="<?= e(url('logout')) ?>">
                        <?= csrf_field() ?>
                        <button class="button button-muted" type="submit">Logout</button>
                    </form>
                </div>
            </header>

            <?php require BASE_PATH . '/app/views/layouts/flash.php'; ?>
            <main class="page-content">
                <?= $content ?>
            </main>
        </div>
    </div>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>

