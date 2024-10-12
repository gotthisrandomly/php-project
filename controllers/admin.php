<?php
require_once '../includes/functions.php';
require_once '../includes/admin_functions.php';

session_start();

if (!is_logged_in() || !is_admin()) {
    header('Location: /login');
    exit;
}

$users = get_all_users();
$game_settings = get_game_settings();
$recent_transactions = get_recent_transactions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_balance'])) {
        $user_id = $_POST['user_id'];
        $new_balance = $_POST['new_balance'];
        update_user_balance($user_id, $new_balance);
    } elseif (isset($_POST['update_settings'])) {
        $slot_machine_max_bet = $_POST['slot_machine_max_bet'];
        $roulette_max_bet = $_POST['roulette_max_bet'];
        update_game_settings($slot_machine_max_bet, $roulette_max_bet);
        $game_settings = get_game_settings();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SD777Slots</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <h2>User Management</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Balance</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['balance']; ?></td>
                <td><?php echo $user['created_at']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="number" name="new_balance" value="<?php echo $user['balance']; ?>" step="0.01">
                        <button type="submit" name="update_balance">Update Balance</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Game Settings</h2>
        <form method="POST">
            <label for="slot_machine_max_bet">Slot Machine Max Bet:</label>
            <input type="number" id="slot_machine_max_bet" name="slot_machine_max_bet" value="<?php echo $game_settings['slot_machine_max_bet']; ?>" step="0.01" required><br>
            
            <label for="roulette_max_bet">Roulette Max Bet:</label>
            <input type="number" id="roulette_max_bet" name="roulette_max_bet" value="<?php echo $game_settings['roulette_max_bet']; ?>" step="0.01" required><br>
            
            <button type="submit" name="update_settings">Update Settings</button>
        </form>

        <h2>Recent Transactions</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Timestamp</th>
            </tr>
            <?php foreach ($recent_transactions as $transaction): ?>
            <tr>
                <td><?php echo $transaction['id']; ?></td>
                <td><?php echo $transaction['user_id']; ?></td>
                <td><?php echo $transaction['amount']; ?></td>
                <td><?php echo $transaction['type']; ?></td>
                <td><?php echo $transaction['timestamp']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p><a href="/">Back to Home</a></p>
    </div>
</body>
</html>