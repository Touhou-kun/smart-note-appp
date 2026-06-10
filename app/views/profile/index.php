<section class="profile-grid">
    <article class="panel">
        <div class="panel-header">
            <div>
                <h2>Account</h2>
                <p>Your registered profile information.</p>
            </div>
        </div>
        <dl class="details-list">
            <div>
                <dt>Username</dt>
                <dd><?= e($user['username'] ?? '') ?></dd>
            </div>
            <div>
                <dt>Email</dt>
                <dd><?= e($user['email'] ?? '') ?></dd>
            </div>
            <div>
                <dt>Registration Date</dt>
                <dd><?= e($user['created_at'] ?? '') ?></dd>
            </div>
        </dl>
    </article>

    <article class="panel">
        <div class="panel-header">
            <div>
                <h2>Change Password</h2>
                <p>Use a minimum of 6 characters.</p>
            </div>
        </div>
        <form class="note-form" method="post" action="<?= e(url('profile/password')) ?>">
            <?= csrf_field() ?>
            <label>Current Password
                <input type="password" name="current_password" required autocomplete="current-password">
            </label>
            <label>New Password
                <input type="password" name="new_password" required minlength="6" autocomplete="new-password">
            </label>
            <label>Confirm New Password
                <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
            </label>
            <button class="button button-primary" type="submit">Change Password</button>
        </form>
    </article>
</section>

