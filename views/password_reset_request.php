<?php require_once 'layout.php'; ?>

<h2>Password Reset Request</h2>
<form action="/password-reset" method="post">
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <input type="submit" value="Request Password Reset">
    </div>
</form>

<p><a href="/login">Back to Login</a></p>