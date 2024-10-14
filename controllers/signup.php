<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Validator.php';

class SignupController {
    public function index() {
        session_start();

        if (is_logged_in()) {
            header('Location: /');
            exit;
        }

        $errors = [];
        $csrf_token = generate_csrf_token();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                $errors[] = "Invalid CSRF token";
            } else {
                $username = Validator::sanitizeInput($_POST['username']);
                $password = $_POST['password'];
                $email = Validator::sanitizeInput($_POST['email']);

                $username_error = Validator::validateUsername($username);
                if ($username_error) $errors[] = $username_error;

                $password_error = Validator::validatePassword($password);
                if ($password_error) $errors[] = $password_error;

                $email_error = Validator::validateEmail($email);
                if ($email_error) $errors[] = $email_error;

                if (empty($errors)) {
                    if (register_user($username, $password, $email)) {
                        $_SESSION['success_message'] = "Account created successfully. Please log in.";
                        header('Location: /login');
                        exit;
                    } else {
                        $errors[] = "Failed to create account. Please try again.";
                        ErrorHandler::logCustomError("Failed to create account for username: $username");
                    }
                }
            }
        }

        $title = 'Sign Up - SD777Slots';
        
        ob_start();
        include __DIR__ . '/../views/signup.php';
        $content = ob_get_clean();

        include __DIR__ . '/../views/layout.php';
    }
}