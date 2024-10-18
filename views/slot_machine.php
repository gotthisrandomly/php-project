<?php
require_once 'includes/functions.php';
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slot Machine Game</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Slot Machine Game</h1>
        <div id="user-info">
            <p>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</p>
            <p>Your balance: $<span id="balance"><?php echo number_format($user['balance'], 2); ?></span></p>
        </div>
        <div id="slot-machine">
            <div id="reels">
                <div class="reel" id="reel1"></div>
                <div class="reel" id="reel2"></div>
                <div class="reel" id="reel3"></div>
            </div>
            <div id="controls">
                <input type="number" id="bet-amount" min="1" step="1" value="1">
                <button id="spin-button">Spin</button>
            </div>
        </div>
        <div id="result"></div>
    </div>
    <script src="assets/js/slot-machine.js"></script>
</body>
</html>