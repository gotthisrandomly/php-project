<?php
require_once '../includes/functions.php';
require_once '../includes/validation.php';

session_start();

if (is_logged_in()) {
    header('Location: /');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token";
    } else {
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];
        $email = sanitize_input($_POST['email']);

        $username_error = validate_username($username);
        if ($username_error) $errors[] = $username_error;

        $password_error = validate_password($password);
        if ($password_error) $errors[] = $password_error;

        $email_error = validate_email($email);
        if ($email_error) $errors[] = $email_error;

        if (empty($errors)) {
            if (register_user($username, $password, $email)) {
                $_SESSION['success_message'] = "Account created successfully. Please log in.";
                header('Location: /login');
                exit;
            } else {
                $errors[] = "Failed to create account. Please try again.";
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - SD777Slots</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 400px; margin: 0 auto; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sign Up</h1>
        <?php
        if (!empty($errors)) {
            echo "<ul class='error'>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
        ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="/login">Log in</a></p>
    </div>
</body>
</html>