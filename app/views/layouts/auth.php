<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-panel">
            <button class="icon-button auth-theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">◐</button>
            <div class="brand-block">
                <span class="brand-mark">SN</span>
                <div>
                    <h1><?= e(APP_NAME) ?></h1>
                    <p>Organize notes, tags, images, and priorities in one secure workspace.</p>
                </div>
            </div>
            <?php require BASE_PATH . '/app/views/layouts/flash.php'; ?>
            <?= $content ?>
        </section>
    </main>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
