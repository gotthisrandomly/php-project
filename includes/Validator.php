<?php
class Validator {
    public static function validateUsername($username) {
        if (strlen($username) < 3 || strlen($username) > 20) {
            return "Username must be between 3 and 20 characters long.";
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return "Username can only contain letters, numbers, and underscores.";
        }
        return null;
    }

    public static function validatePassword($password) {
        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return "Password must contain at least one uppercase letter, one lowercase letter, and one number.";
        }
        return null;
    }

    public static function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }
        return null;
    }

    public static function validateBetAmount($betAmount, $balance) {
        if (!is_numeric($betAmount) || $betAmount <= 0) {
            return "Bet amount must be a positive number.";
        }
        if ($betAmount > $balance) {
            return "Bet amount cannot exceed your balance.";
        }
        return null;
    }

    public static function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public static function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
}