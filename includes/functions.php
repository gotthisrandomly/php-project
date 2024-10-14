<?php
require_once __DIR__ . '/Database.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function login_user($username, $password) {
    $db = Database::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE username = ?", [$username]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }

    return false;
}

function register_user($username, $password, $email) {
    $db = Database::getInstance();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $db->execute(
            "INSERT INTO users (username, password, email) VALUES (?, ?, ?)",
            [$username, $hashed_password, $email]
        );
        return true;
    } catch (PDOException $e) {
        error_log("Error registering user: " . $e->getMessage());
        return false;
    }
}

function get_user_balance($user_id) {
    $db = Database::getInstance();
    $result = $db->fetch("SELECT balance FROM users WHERE id = ?", [$user_id]);
    return $result ? $result['balance'] : 0;
}

function update_user_balance($user_id, $amount) {
    $db = Database::getInstance();
    return $db->execute(
        "UPDATE users SET balance = balance + ? WHERE id = ?",
        [$amount, $user_id]
    );
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function log_game_result($user_id, $game_type, $bet_amount, $result_amount) {
    $db = Database::getInstance();
    return $db->execute(
        "INSERT INTO game_logs (user_id, game_type, bet_amount, result_amount) VALUES (?, ?, ?, ?)",
        [$user_id, $game_type, $bet_amount, $result_amount]
    );
}

function get_user_game_history($user_id, $limit = 10) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM game_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT ?",
        [$user_id, $limit]
    );
}