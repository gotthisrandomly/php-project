<?php
function register_user($username, $password) {
    global $pdo;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['username' => $username, 'password' => $hashed_password]);
}

function login_user($username, $password) {
    global $pdo;
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_balance($user_id) {
    global $pdo;
    $sql = "SELECT balance FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $result = $stmt->fetch();
    return $result ? $result['balance'] : 0;
}

function update_balance($user_id, $amount) {
    global $pdo;
    $sql = "UPDATE users SET balance = balance + :amount WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['amount' => $amount, 'user_id' => $user_id]);
}

function play_slot_machine($user_id, $bet) {
    // Implement slot machine logic here
    // Return win amount or 0 if lost
    $win_amount = rand(0, $bet * 2); // Simple random win logic for demonstration
    if ($win_amount > 0) {
        update_balance($user_id, $win_amount - $bet);
        record_transaction($user_id, $win_amount, 'win');
    } else {
        update_balance($user_id, -$bet);
        record_transaction($user_id, $bet, 'bet');
    }
    return $win_amount;
}

function record_transaction($user_id, $amount, $type) {
    global $pdo;
    $sql = "INSERT INTO transactions (user_id, amount, type) VALUES (:user_id, :amount, :type)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['user_id' => $user_id, 'amount' => $amount, 'type' => $type]);
}