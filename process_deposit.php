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

    // Fetch min and max deposit amounts from settings
    $stmt = $pdo->query("SELECT value FROM settings WHERE key IN ('min_deposit_amount', 'max_deposit_amount')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $min_deposit = floatval($settings['min_deposit_amount']);
    $max_deposit = floatval($settings['max_deposit_amount']);

    if ($amount < $min_deposit || $amount > $max_deposit) {
        $_SESSION['error'] = "Deposit amount must be between {$min_deposit} and {$max_deposit}.";
        header("Location: deposit.php");
        exit();
    }

    // Process the deposit (this is a simplified version, you'd typically integrate with a payment gateway here)
    $stmt = $pdo->prepare("INSERT INTO deposits (user_id, amount, method, status) VALUES (?, ?, ?, 'pending')");
    if ($stmt->execute([$_SESSION['user_id'], $amount, $method])) {
        $_SESSION['success'] = "Deposit request submitted successfully.";
    } else {
        $_SESSION['error'] = "Error processing deposit. Please try again.";
    }

    header("Location: deposit.php");
    exit();
}
?>