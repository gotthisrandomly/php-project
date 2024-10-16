<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header>
        <h1>Admin Panel</h1>
        <nav>
            <ul>
                <li><a href="index.php?action=manage_deposits">Manage Deposits</a></li>
                <li><a href="index.php?action=manage_withdrawals">Manage Withdrawals</a></li>
                <li><a href="index.php?action=manage_payment_gateways">Manage Payment Gateways</a></li>
                <li><a href="index.php?action=manage_deposit_methods">Manage Deposit Methods</a></li>
                <li><a href="index.php?action=manage_withdrawal_methods">Manage Withdrawal Methods</a></li>
                <li><a href="index.php?action=settings">Settings</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php
        switch ($action) {
            case 'manage_deposits':
                include 'manage_deposits.php';
                break;
            case 'manage_withdrawals':
                include 'manage_withdrawals.php';
                break;
            case 'manage_payment_gateways':
                include 'manage_payment_gateways.php';
                break;
            case 'manage_deposit_methods':
                include 'manage_deposit_methods.php';
                break;
            case 'manage_withdrawal_methods':
                include 'manage_withdrawal_methods.php';
                break;
            case 'settings':
                include 'settings.php';
                break;
            default:
                include 'dashboard.php';
        }
        ?>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Your Company Name</p>
    </footer>
</body>
</html>