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

    public static function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public static function validateBet($amount, $min, $max) {
        $amount = filter_var($amount, FILTER_VALIDATE_INT);
        if ($amount === false || $amount < $min || $amount > $max) {
            return "Bet amount must be between $min and $max.";
        }
        return null;
    }
}