<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Online Casino'; ?> - SD777Slots</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .error { color: red; }
        .success { color: green; }
        .oauth-buttons { margin-top: 20px; }
        .oauth-button { display: inline-block; padding: 10px 20px; margin-right: 10px; text-decoration: none; color: #fff; border-radius: 5px; }
        .google { background-color: #DB4437; }
        .facebook { background-color: #4267B2; }
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="/">SD777Slots</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="/slot-machine">Slot Machine</a></li>
                    <li class="nav-item"><a class="nav-link" href="/roulette">Roulette</a></li>
                    <li class="nav-item"><a class="nav-link" href="/blackjack">Blackjack</a></li>
                    <li class="nav-item"><a class="nav-link" href="/deposit">Deposit</a></li>
                    <li class="nav-item"><a class="nav-link" href="/responsible-gambling">Responsible Gambling</a></li>
                    <li class="nav-item"><a class="nav-link" href="/logout">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/signup">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($content)) { echo $content; } ?>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container">
            <p class="text-center">&copy; <?php echo date('Y'); ?> SD777Slots. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>