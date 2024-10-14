<div class="container">
    <h1>Login</h1>
    <?php
    if (!empty($errors)) {
        echo "<ul class='error'>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
    if (isset($_SESSION['success_message'])) {
        echo "<p class='success'>" . $_SESSION['success_message'] . "</p>";
        unset($_SESSION['success_message']);
    }
    ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        
        <button type="submit">Login</button>
    </form>
    <div class="oauth-buttons">
        <a href="<?php echo get_google_auth_url(); ?>" class="oauth-button google">Login with Google</a>
        <a href="<?php echo get_facebook_auth_url(); ?>" class="oauth-button facebook">Login with Facebook</a>
    </div>
    <p>Don't have an account? <a href="/signup">Sign up</a></p>
</div>