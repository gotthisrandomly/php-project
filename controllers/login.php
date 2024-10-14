<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/oauth.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/ErrorHandler.php';

class LoginController {
    private $db;
    private $max_attempts = 5;
    private $lockout_time = 900; // 15 minutes

    public function __construct() {
        $this->db = new Database();
    }

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
                ErrorHandler::logCustomError("Invalid CSRF token attempt");
            } else {
                $username = sanitize_input($_POST['username']);
                $password = $_POST['password'];

                if (empty($username) || empty($password)) {
                    $errors[] = "Username and password are required.";
                } else {
                    if ($this->check_brute_force($username)) {
                        $errors[] = "Too many failed attempts. Please try again later.";
                        ErrorHandler::logCustomError("Brute force attempt detected for user: $username");
                    } else {
                        if ($this->verify_login($username, $password)) {
                            $this->reset_login_attempts($username);
                            $_SESSION['user_id'] = $username; // In a real app, use a unique user ID
                            header('Location: /');
                            exit;
                        } else {
                            $this->increment_login_attempts($username);
                            $errors[] = "Invalid username or password.";
                            ErrorHandler::logCustomError("Failed login attempt for user: $username");
                        }
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

    private function verify_login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                return password_verify($password, $user['password']);
            }
            return false;
        } catch (Exception $e) {
            ErrorHandler::logCustomError("Database error in verify_login: " . $e->getMessage());
            return false;
        }
    }

    private function check_brute_force($username) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE username = ? AND time > ?");
            $time = time() - $this->lockout_time;
            $stmt->bind_param("si", $username, $time);
            $stmt->execute();
            $result = $stmt->get_result();
            $attempts = $result->fetch_assoc()['attempts'];
            return $attempts >= $this->max_attempts;
        } catch (Exception $e) {
            ErrorHandler::logCustomError("Database error in check_brute_force: " . $e->getMessage());
            return false;
        }
    }

    private function increment_login_attempts($username) {
        try {
            $stmt = $this->db->prepare("INSERT INTO login_attempts (username, time) VALUES (?, ?)");
            $time = time();
            $stmt->bind_param("si", $username, $time);
            $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logCustomError("Database error in increment_login_attempts: " . $e->getMessage());
        }
    }

    private function reset_login_attempts($username) {
        try {
            $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logCustomError("Database error in reset_login_attempts: " . $e->getMessage());
        }
    }
    }
}