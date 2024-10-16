<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $method = filter_input(INPUT_POST, 'method', FILTER_SANITIZE_STRING);

    // Fetch settings
    $stmt = $pdo->query("SELECT * FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $min_withdrawal = floatval($settings['min_withdrawal_amount']);
    $max_withdrawal = floatval($settings['max_withdrawal_amount']);
    $restriction_percentage = floatval($settings['withdrawal_restriction_percentage']);

    if ($amount < $min_withdrawal || $amount > $max_withdrawal) {
        $_SESSION['error'] = "Withdrawal amount must be between {$min_withdrawal} and {$max_withdrawal}.";
        header("Location: withdrawal.php");
        exit();
    }

    // Check user's balance and total deposits
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_balance = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT SUM(amount) FROM deposits WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$_SESSION['user_id']]);
    $total_deposits = $stmt->fetchColumn();

    // Calculate the restricted amount
    $restricted_amount = $total_deposits * ($restriction_percentage / 100);

    if ($user_balance - $amount < $restricted_amount) {
        $_SESSION['error'] = "Insufficient funds. You must keep at least " . number_format($restricted_amount, 2) . " in your account.";
        header("Location: withdrawal.php");
        exit();
    }

    // Process the withdrawal
    $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, method, status) VALUES (?, ?, ?, 'pending')");
    if ($stmt->execute([$_SESSION['user_id'], $amount, $method])) {
        // Update user's balance
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$amount, $_SESSION['user_id']]);
        $_SESSION['success'] = "Withdrawal request submitted successfully.";
    } else {
        $_SESSION['error'] = "Error processing withdrawal. Please try again.";
    }

    header("Location: withdrawal.php");
    exit();
}
?>