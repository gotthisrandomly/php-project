<?php
require_once '../includes/functions.php';
require_once '../includes/slot_machine.php';
require_once '../includes/validation.php';
require_once '../includes/rate_limiter.php';

session_start();

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user_id = $_SESSION['user_id'];
$balance = get_user_balance($user_id);

$result = null;
$win_amount = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } elseif (is_rate_limited($user_id, 'slot_machine')) {
        $error = "You're playing too fast. Please wait a moment before spinning again.";
    } else {
        $bet_amount = floatval($_POST['bet_amount']);
        $bet_error = validate_bet_amount($bet_amount, $balance);
        
        if ($bet_error) {
            $error = $bet_error;
        } else {
            $game_result = playSlotMachine($user_id, $bet_amount);
            $result = $game_result['result'];
            $win_amount = $game_result['win_amount'];
            $balance = get_user_balance($user_id); // Update balance after the game
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slot Machine - SD777Slots</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .slot-result { font-size: 48px; text-align: center; margin: 20px 0; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Slot Machine</h1>
        <p>Your balance: $<?php echo number_format($balance, 2); ?></p>
        
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <?php if ($result): ?>
            <div class="slot-result"><?php echo implode(' ', $result); ?></div>
            <p>You <?php echo ($win_amount > 0) ? "won $" . number_format($win_amount, 2) : "lost $" . number_format($bet_amount, 2); ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <label for="bet_amount">Bet Amount:</label>
            <input type="number" id="bet_amount" name="bet_amount" step="0.01" min="1" max="<?php echo $balance; ?>" required><br>
            
            <button type="submit">Spin</button>
        </form>
        
        <p><a href="/">Back to Home</a></p>
    </div>
</body>
</html>