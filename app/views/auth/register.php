<form class="form-card" method="post" action="<?= e(url('register')) ?>">
    <?= csrf_field() ?>
    <div class="form-heading">
        <h2>Register</h2>
        <p>Create your personal note workspace.</p>
    </div>
    <label>Username
        <input type="text" name="username" required autocomplete="name" maxlength="100">
    </label>
    <label>Email
        <input type="email" name="email" required autocomplete="email">
    </label>
    <label>Password
        <input type="password" name="password" required minlength="6" autocomplete="new-password">
    </label>
    <label>Confirm Password
        <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
    </label>
    <button class="button button-primary full" type="submit">Create Account</button>
    <p class="auth-link">Already registered? <a href="<?= e(url('login')) ?>">Login</a></p>
</form>

