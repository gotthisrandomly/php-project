<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Fetch basic statistics
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_games = $pdo->query("SELECT COUNT(*) FROM game_history")->fetchColumn();
$total_deposits = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'deposit'")->fetchColumn();
$total_withdrawals = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'withdrawal'")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Admin Dashboard</h1>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text"><?php echo $total_users; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Games</h5>
                        <p class="card-text"><?php echo $total_games; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Deposits</h5>
                        <p class="card-text">$<?php echo number_format($total_deposits, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Withdrawals</h5>
                        <p class="card-text">$<?php echo number_format($total_withdrawals, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <h2>Quick Actions</h2>
            <a href="user_management.php" class="btn btn-primary">Manage Users</a>
            <a href="game_history.php" class="btn btn-secondary">View Game History</a>
            <a href="slot_settings.php" class="btn btn-info">Slot Machine Settings</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>