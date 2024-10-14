<?php require_once 'layout.php'; ?>

<h2>Reset Password</h2>
<form action="/password-reset/reset" method="post">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <div>
        <label for="password">New Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <div>
        <input type="submit" value="Reset Password">
    </div>
</form>

<p><a href="/login">Back to Login</a></p>