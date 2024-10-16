<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's balance
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$balance = $user['balance'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Payment Panel</title>
</head>
<body>
    <h1>Player Payment Panel</h1>
    <p>Current Balance: $<?php echo number_format($balance, 2); ?></p>
    
    <h2>Deposit</h2>
    <form action="process_deposit.php" method="post">
        <label for="deposit_amount">Amount:</label>
        <input type="number" id="deposit_amount" name="amount" min="10" max="1000" step="0.01" required>
        <button type="submit">Deposit</button>
    </form>

    <h2>Withdraw</h2>
    <form action="process_withdrawal.php" method="post">
        <label for="withdraw_amount">Amount:</label>
        <input type="number" id="withdraw_amount" name="amount" min="10" max="1000" step="0.01" required>
        <button type="submit">Withdraw</button>
    </form>
</body>
</html>