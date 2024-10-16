<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

// Check if user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Function to handle POST requests
function handlePost($pdo) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'manage_deposit':
                $id = $_POST['id'];
                $status = $_POST['status'];
                $stmt = $pdo->prepare("UPDATE deposits SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                if ($status === 'completed') {
                    // Update user balance
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance + (SELECT amount FROM deposits WHERE id = ?) WHERE id = (SELECT user_id FROM deposits WHERE id = ?)");
                    $stmt->execute([$id, $id]);
                }
                break;
            case 'manage_withdrawal':
                $id = $_POST['id'];
                $status = $_POST['status'];
                $stmt = $pdo->prepare("UPDATE withdrawals SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                if ($status === 'rejected') {
                    // Refund user balance
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance + (SELECT amount FROM withdrawals WHERE id = ?) WHERE id = (SELECT user_id FROM withdrawals WHERE id = ?)");
                    $stmt->execute([$id, $id]);
                }
                break;
            case 'manage_payment_gateway':
                $id = $_POST['id'];
                $reference_currency = $_POST['reference_currency'];
                $rate = $_POST['rate'];
                $stmt = $pdo->prepare("UPDATE payment_gateways SET reference_currency = ?, rate = ? WHERE id = ?");
                $stmt->execute([$reference_currency, $rate, $id]);
                break;
            case 'manage_deposit_method':
                $name = $_POST['name'];
                $min_amount = $_POST['min_amount'];
                $max_amount = $_POST['max_amount'];
                $enabled = isset($_POST['enabled']) ? 1 : 0;
                if (isset($_POST['id'])) {
                    $stmt = $pdo->prepare("UPDATE deposit_methods SET name = ?, min_amount = ?, max_amount = ?, enabled = ? WHERE id = ?");
                    $stmt->execute([$name, $min_amount, $max_amount, $enabled, $_POST['id']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO deposit_methods (name, min_amount, max_amount, enabled) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $min_amount, $max_amount, $enabled]);
                }
                break;
            case 'manage_withdrawal_method':
                $name = $_POST['name'];
                $min_amount = $_POST['min_amount'];
                $max_amount = $_POST['max_amount'];
                $enabled = isset($_POST['enabled']) ? 1 : 0;
                if (isset($_POST['id'])) {
                    $stmt = $pdo->prepare("UPDATE withdrawal_methods SET name = ?, min_amount = ?, max_amount = ?, enabled = ? WHERE id = ?");
                    $stmt->execute([$name, $min_amount, $max_amount, $enabled, $_POST['id']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO withdrawal_methods (name, min_amount, max_amount, enabled) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $min_amount, $max_amount, $enabled]);
                }
                break;
            case 'set_deposit_limits':
                $min_deposit = $_POST['min_deposit'];
                $max_deposit = $_POST['max_deposit'];
                $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'min_deposit'");
                $stmt->execute([$min_deposit]);
                $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'max_deposit'");
                $stmt->execute([$max_deposit]);
                break;
            case 'set_withdrawal_limits':
                $min_withdrawal = $_POST['min_withdrawal'];
                $max_withdrawal = $_POST['max_withdrawal'];
                $withdrawal_restriction = $_POST['withdrawal_restriction'];
                $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'min_withdrawal'");
                $stmt->execute([$min_withdrawal]);
                $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'max_withdrawal'");
                $stmt->execute([$max_withdrawal]);
                $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'withdrawal_restriction'");
                $stmt->execute([$withdrawal_restriction]);
                break;
        }
    }
}

// Handle POST request
handlePost($pdo);

// Fetch data for display
$deposits = $pdo->query("SELECT * FROM deposits WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
$withdrawals = $pdo->query("SELECT * FROM withdrawals WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
$payment_gateways = $pdo->query("SELECT * FROM payment_gateways")->fetchAll();
$deposit_methods = $pdo->query("SELECT * FROM deposit_methods")->fetchAll();
$withdrawal_methods = $pdo->query("SELECT * FROM withdrawal_methods")->fetchAll();
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .section { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>

    <div class="section">
        <h2>Manage Deposits</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
            <?php foreach ($deposits as $deposit): ?>
            <tr>
                <td><?php echo $deposit['id']; ?></td>
                <td><?php echo $deposit['user_id']; ?></td>
                <td><?php echo $deposit['amount']; ?></td>
                <td><?php echo $deposit['method']; ?></td>
                <td><?php echo $deposit['created_at']; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="manage_deposit">
                        <input type="hidden" name="id" value="<?php echo $deposit['id']; ?>">
                        <select name="status">
                            <option value="completed">Complete</option>
                            <option value="rejected">Reject</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="section">
        <h2>Manage Withdrawals</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
            <?php foreach ($withdrawals as $withdrawal): ?>
            <tr>
                <td><?php echo $withdrawal['id']; ?></td>
                <td><?php echo $withdrawal['user_id']; ?></td>
                <td><?php echo $withdrawal['amount']; ?></td>
                <td><?php echo $withdrawal['method']; ?></td>
                <td><?php echo $withdrawal['created_at']; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="manage_withdrawal">
                        <input type="hidden" name="id" value="<?php echo $withdrawal['id']; ?>">
                        <select name="status">
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                            <option value="completed">Complete</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="section">
        <h2>Manage Payment Gateways</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Reference Currency</th>
                <th>Rate</th>
                <th>Action</th>
            </tr>
            <?php foreach ($payment_gateways as $gateway): ?>
            <tr>
                <td><?php echo $gateway['id']; ?></td>
                <td><?php echo $gateway['name']; ?></td>
                <td><?php echo $gateway['reference_currency']; ?></td>
                <td><?php echo $gateway['rate']; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="manage_payment_gateway">
                        <input type="hidden" name="id" value="<?php echo $gateway['id']; ?>">
                        <input type="text" name="reference_currency" value="<?php echo $gateway['reference_currency']; ?>" required>
                        <input type="number" name="rate" value="<?php echo $gateway['rate']; ?>" step="0.01" required>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="section">
        <h2>Manage Deposit Methods</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Min Amount</th>
                <th>Max Amount</th>
                <th>Enabled</th>
                <th>Action</th>
            </tr>
            <?php foreach ($deposit_methods as $method): ?>
            <tr>
                <td><?php echo $method['id']; ?></td>
                <td><?php echo $method['name']; ?></td>
                <td><?php echo $method['min_amount']; ?></td>
                <td><?php echo $method['max_amount']; ?></td>
                <td><?php echo $method['enabled'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="manage_deposit_method">
                        <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
                        <input type="text" name="name" value="<?php echo $method['name']; ?>" required>
                        <input type="number" name="min_amount" value="<?php echo $method['min_amount']; ?>" required>
                        <input type="number" name="max_amount" value="<?php echo $method['max_amount']; ?>" required>
                        <input type="checkbox" name="enabled" <?php echo $method['enabled'] ? 'checked' : ''; ?>>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <form method="post">
            <input type="hidden" name="action" value="manage_deposit_method">
            <input type="text" name="name" placeholder="Name" required>
            <input type="number" name="min_amount" placeholder="Min Amount" required>
            <input type="number" name="max_amount" placeholder="Max Amount" required>
            <input type="checkbox" name="enabled"> Enabled
            <button type="submit">Create New Deposit Method</button>
        </form>
    </div>

    <div class="section">
        <h2>Manage Withdrawal Methods</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Min Amount</th>
                <th>Max Amount</th>
                <th>Enabled</th>
                <th>Action</th>
            </tr>
            <?php foreach ($withdrawal_methods as $method): ?>
            <tr>
                <td><?php echo $method['id']; ?></td>
                <td><?php echo $method['name']; ?></td>
                <td><?php echo $method['min_amount']; ?></td>
                <td><?php echo $method['max_amount']; ?></td>
                <td><?php echo $method['enabled'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="manage_withdrawal_method">
                        <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
                        <input type="text" name="name" value="<?php echo $method['name']; ?>" required>
                        <input type="number" name="min_amount" value="<?php echo $method['min_amount']; ?>" required>
                        <input type="number" name="max_amount" value="<?php echo $method['max_amount']; ?>" required>
                        <input type="checkbox" name="enabled" <?php echo $method['enabled'] ? 'checked' : ''; ?>>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <form method="post">
            <input type="hidden" name="action" value="manage_withdrawal_method">
            <input type="text" name="name" placeholder="Name" required>
            <input type="number" name="min_amount" placeholder="Min Amount" required>
            <input type="number" name="max_amount" placeholder="Max Amount" required>
            <input type="checkbox" name="enabled"> Enabled
            <button type="submit">Create New Withdrawal Method</button>
        </form>
    </div>

    <div class="section">
        <h2>Set Deposit/Withdrawal Limits</h2>
        <form method="post">
            <input type="hidden" name="action" value="set_deposit_limits">
            <label>Min Deposit: <input type="number" name="min_deposit" value="<?php echo $settings['min_deposit']; ?>" required></label>
            <label>Max Deposit: <input type="number" name="max_deposit" value="<?php echo $settings['max_deposit']; ?>" required></label>
            <button type="submit">Update Deposit Limits</button>
        </form>
        <form method="post">
            <input type="hidden" name="action" value="set_withdrawal_limits">
            <label>Min Withdrawal: <input type="number" name="min_withdrawal" value="<?php echo $settings['min_withdrawal']; ?>" required></label>
            <label>Max Withdrawal: <input type="number" name="max_withdrawal" value="<?php echo $settings['max_withdrawal']; ?>" required></label>
            <label>Withdrawal Restriction (%): <input type="number" name="withdrawal_restriction" value="<?php echo $settings['withdrawal_restriction']; ?>" required></label>
            <button type="submit">Update Withdrawal Limits</button>
        </form>
    </div>
</body>
</html>