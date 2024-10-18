<?php
require_once __DIR__ . '/Database.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function login_user($username, $password) {
    $db = Database::getInstance();
    $users = $db->fetchAll('users');
    $user = array_filter($users, function($u) use ($username) {
        return $u['username'] === $username;
    });

    if ($user && password_verify($password, reset($user)['password'])) {
        $_SESSION['user_id'] = key($user);
        $_SESSION['username'] = $username;
        return true;
    }

    return false;
}

function register_user($username, $password, $email) {
    $db = Database::getInstance();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $user_data = [
        'username' => $username,
        'password' => $hashed_password,
        'email' => $email,
        'balance' => 0
    ];

    try {
        $db->execute('users', $user_data);
        return true;
    } catch (Exception $e) {
        error_log("Error registering user: " . $e->getMessage());
        return false;
    }
}

function get_user_balance($user_id) {
    $db = Database::getInstance();
    $user = $db->fetch('users', $user_id);
    return $user ? $user['balance'] : 0;
}

function updateUserBalance($user_id, $new_balance) {
    $db = Database::getInstance();
    $user = $db->fetch('users', $user_id);
    if ($user) {
        $user['balance'] = $new_balance;
        $db->execute('users', $user);
        return true;
    }
    return false;
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
    $log_data = [
        'user_id' => $user_id,
        'game_type' => $game_type,
        'bet_amount' => $bet_amount,
        'result_amount' => $result_amount,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    return $db->execute('game_logs', $log_data);
}

function get_user_game_history($user_id, $limit = 10) {
    $db = Database::getInstance();
    $all_logs = $db->fetchAll('game_logs');
    $user_logs = array_filter($all_logs, function($log) use ($user_id) {
        return $log['user_id'] === $user_id;
    });
    usort($user_logs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    return array_slice($user_logs, 0, $limit);
}

function getCurrentUser() {
    if (!is_logged_in()) {
        return null;
    }
    $db = Database::getInstance();
    return $db->fetch('users', $_SESSION['user_id']);
}

function appendToJsonFile($filename, $data) {
    $filePath = __DIR__ . '/../data/' . $filename;
    $currentData = [];
    if (file_exists($filePath)) {
        $currentData = json_decode(file_get_contents($filePath), true) ?? [];
    }
    $currentData[] = $data;
    file_put_contents($filePath, json_encode($currentData, JSON_PRETTY_PRINT));
}