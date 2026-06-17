<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? APP_NAME) . ' - ' . APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
    <main class="public-shell">
        <section class="panel public-panel">
            <a class="brand" href="<?= e(url('login')) ?>">
                <span class="brand-mark">SN</span>
                <span>Smart Note</span>
            </a>
            <?= $content ?>
        </section>
    </main>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
