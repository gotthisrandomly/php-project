<?php

class PasswordResetController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePasswordResetRequest();
        } else {
            $this->loadView('password_reset_request');
        }
    }

    public function reset() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePasswordReset();
        } else {
            $token = $_GET['token'] ?? '';
            if ($this->validateToken($token)) {
                $this->loadView('password_reset', ['token' => $token]);
            } else {
                $_SESSION['error'] = "Invalid or expired token.";
                header('Location: /login');
                exit;
            }
        }
    }

    private function handlePasswordResetRequest() {
        $email = $_POST['email'] ?? '';
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $this->db->prepare("INSERT INTO password_reset_tokens (email, token, expiry) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expiry);
            
            if ($stmt->execute()) {
                $this->sendPasswordResetEmail($email, $token);
                $_SESSION['success'] = "Password reset instructions have been sent to your email.";
                Logger::info("Password reset requested for email: $email");
            } else {
                $_SESSION['error'] = "An error occurred. Please try again later.";
                Logger::error("Failed to create password reset token for email: $email");
            }
        } else {
            $_SESSION['error'] = "Invalid email address.";
        }
        header('Location: /login');
        exit;
    }

    private function handlePasswordReset() {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Passwords do not match.";
            header('Location: /password-reset/reset?token=' . urlencode($token));
            exit;
        }

        if ($this->validateToken($token)) {
            $stmt = $this->db->prepare("SELECT email FROM password_reset_tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row) {
                $email = $row['email'];
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss", $hashedPassword, $email);
                
                if ($stmt->execute()) {
                    $this->deleteToken($token);
                    $_SESSION['success'] = "Your password has been reset successfully.";
                    Logger::info("Password reset successful for email: $email");
                    header('Location: /login');
                    exit;
                }
            }
        }

        $_SESSION['error'] = "An error occurred. Please try again.";
        Logger::error("Password reset failed for token: $token");
        header('Location: /login');
        exit;
    }

    private function validateToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM password_reset_tokens WHERE token = ? AND expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    private function deleteToken($token) {
        $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
    }

    private function sendPasswordResetEmail($email, $token) {
        $resetLink = "https://fcs9t2bt.enginelabs.app/password-reset/reset?token=" . urlencode($token);
        $subject = "Password Reset Request";
        $message = "To reset your password, please click on the following link:\n\n$resetLink\n\nThis link will expire in 1 hour.";
        $headers = "From: noreply@yourdomain.com";

        if (mail($email, $subject, $message, $headers)) {
            Logger::info("Password reset email sent to: $email");
        } else {
            Logger::error("Failed to send password reset email to: $email");
        }
    }

    private function loadView($viewName, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/$viewName.php";
    }
}