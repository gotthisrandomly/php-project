<?php
// Assume user authentication is handled in the controller
$user = isset($user) ? $user : ['username' => 'Guest', 'balance' => 1000];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($title); ?></h1>
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
                <input type="number" id="bet-amount" min="1" max="100" step="1" value="1">
                <button id="spin-button">Spin</button>
            </div>
        </div>
        <div id="result"></div>
        <div id="payout-table">
            <h3>Payout Table</h3>
            <table>
                <tr><th>Combination</th><th>Payout</th></tr>
                <?php foreach ($payoutTable as $combination => $payout): ?>
                    <tr><td><?php echo $combination; ?></td><td><?php echo $payout; ?>x</td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <script>
        const symbols = <?php echo $symbols; ?>;
        const payoutTable = <?php echo $payoutTable; ?>;
    </script>
    <script src="/assets/js/slot-machine.js"></script>
</body>
</html>