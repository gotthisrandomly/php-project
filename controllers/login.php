<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/oauth.php';

class LoginController {
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
                $username = sanitize_input($_POST['username']);
                $password = $_POST['password'];

                if (empty($username) || empty($password)) {
                    $errors[] = "Username and password are required.";
                } else {
                    if (login_user($username, $password)) {
                        header('Location: /');
                        exit;
                    } else {
                        $errors[] = "Invalid username or password.";
                    }
                }
            }
        }

        $title = 'Login - SD777Slots';
        
        ob_start();
        include __DIR__ . '/../views/login.php';
        $content = ob_get_clean();

        include __DIR__ . '/../views/layout.php';
    }
}