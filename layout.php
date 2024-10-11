<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - SD777Slots</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        nav { background-color: #333; color: #fff; padding: 10px; }
        nav ul { list-style-type: none; padding: 0; }
        nav ul li { display: inline; margin-right: 10px; }
        nav ul li a { color: #fff; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="/">Home</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="/slot-machine">Slot Machine</a></li>
                <li><a href="/roulette">Roulette</a></li>
                <li><a href="/blackjack">Blackjack</a></li>
                <li><a href="/deposit">Deposit</a></li>
                <li><a href="/responsible-gambling">Responsible Gambling</a></li>
                <li><a href="/logout">Logout</a></li>
            <?php else: ?>
                <li><a href="/login">Login</a></li>
                <li><a href="/signup">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container">
        <?php echo $content; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> SD777Slots. All rights reserved.</p>
    </footer>
</body>
</html>