<form class="form-card" method="post" action="<?= e(url('login')) ?>">
    <?= csrf_field() ?>
    <div class="form-heading">
        <h2>Login</h2>
        <p>Access your private notes.</p>
    </div>
    <label>Email
        <input type="email" name="email" required autocomplete="email">
    </label>
    <label>Password
        <input type="password" name="password" required autocomplete="current-password">
    </label>
    <button class="button button-primary full" type="submit">Login</button>
    <p class="auth-link">No account yet? <a href="<?= e(url('register')) ?>">Create one</a></p>
</form>

