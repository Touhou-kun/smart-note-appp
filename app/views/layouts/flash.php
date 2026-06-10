<?php foreach (consume_flash() as $message): ?>
    <div class="toast toast-<?= e($message['type']) ?>" data-toast>
        <?= e($message['message']) ?>
    </div>
<?php endforeach; ?>

