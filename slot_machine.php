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

$message = '';
$win_amount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bet_amount = $_POST['bet_amount'];
    
    if ($bet_amount > $balance) {
        $message = "You don't have enough balance to place this bet.";
    } else {
        // Deduct bet amount from balance
        $new_balance = $balance - $bet_amount;
        $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $stmt->execute([$new_balance, $user_id]);
        
        // Generate random symbols
        $symbols = ['🍒', '🍋', '🍊', '🍇', '7️⃣'];
        $result = [
            rand(0, 4),
            rand(0, 4),
            rand(0, 4)
        ];
        
        // Check for win
        if ($result[0] === $result[1] && $result[1] === $result[2]) {
            // Jackpot
            $win_amount = $bet_amount * 10;
        } elseif ($result[0] === $result[1] || $result[1] === $result[2] || $result[0] === $result[2]) {
            // Two matching symbols
            $win_amount = $bet_amount * 2;
        }
        
        // Update balance with winnings
        $new_balance += $win_amount;
        $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $stmt->execute([$new_balance, $user_id]);
        
        $balance = $new_balance;
        $message = $win_amount > 0 ? "You won $win_amount!" : "Sorry, you didn't win this time.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slot Machine</title>
    <style>
        .slot-machine {
            font-size: 48px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Slot Machine</h1>
    <p>Current Balance: $<?php echo number_format($balance, 2); ?></p>
    
    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    
    <?php if (isset($result)): ?>
        <div class="slot-machine">
            <?php echo $symbols[$result[0]] . $symbols[$result[1]] . $symbols[$result[2]]; ?>
        </div>
    <?php endif; ?>
    
    <form method="post">
        <label for="bet_amount">Bet Amount:</label>
        <input type="number" id="bet_amount" name="bet_amount" min="1" max="100" step="1" required>
        <button type="submit">Spin</button>
    </form>
    
    <p><a href="player_payment_panel.php">Back to Payment Panel</a></p>
</body>
</html>