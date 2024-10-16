<?php
// Fetch current settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateSettings = [
        'min_deposit_amount' => $_POST['min_deposit_amount'],
        'max_deposit_amount' => $_POST['max_deposit_amount'],
        'min_withdrawal_amount' => $_POST['min_withdrawal_amount'],
        'max_withdrawal_amount' => $_POST['max_withdrawal_amount'],
        'withdrawal_restriction_percentage' => $_POST['withdrawal_restriction_percentage']
    ];

    foreach ($updateSettings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT (key) DO UPDATE SET value = ?");
        $stmt->execute([$key, $value, $value]);
    }

    // Redirect to refresh the page
    header("Location: index.php?action=settings");
    exit();
}
?>

<h2>Settings</h2>

<form method="POST">
    <label for="min_deposit_amount">Minimum Deposit Amount:</label>
    <input type="number" id="min_deposit_amount" name="min_deposit_amount" step="0.01" value="<?php echo htmlspecialchars($settings['min_deposit_amount'] ?? ''); ?>" required>

    <label for="max_deposit_amount">Maximum Deposit Amount:</label>
    <input type="number" id="max_deposit_amount" name="max_deposit_amount" step="0.01" value="<?php echo htmlspecialchars($settings['max_deposit_amount'] ?? ''); ?>" required>

    <label for="min_withdrawal_amount">Minimum Withdrawal Amount:</label>
    <input type="number" id="min_withdrawal_amount" name="min_withdrawal_amount" step="0.01" value="<?php echo htmlspecialchars($settings['min_withdrawal_amount'] ?? ''); ?>" required>

    <label for="max_withdrawal_amount">Maximum Withdrawal Amount:</label>
    <input type="number" id="max_withdrawal_amount" name="max_withdrawal_amount" step="0.01" value="<?php echo htmlspecialchars($settings['max_withdrawal_amount'] ?? ''); ?>" required>

    <label for="withdrawal_restriction_percentage">Withdrawal Restriction Percentage:</label>
    <input type="number" id="withdrawal_restriction_percentage" name="withdrawal_restriction_percentage" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($settings['withdrawal_restriction_percentage'] ?? ''); ?>" required>
    <small>This percentage of the total deposited amount must remain in the account after withdrawal.</small>

    <button type="submit">Update Settings</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        const minDeposit = parseFloat(document.getElementById('min_deposit_amount').value);
        const maxDeposit = parseFloat(document.getElementById('max_deposit_amount').value);
        const minWithdrawal = parseFloat(document.getElementById('min_withdrawal_amount').value);
        const maxWithdrawal = parseFloat(document.getElementById('max_withdrawal_amount').value);

        if (minDeposit > maxDeposit) {
            alert('Minimum deposit amount cannot be greater than maximum deposit amount.');
            event.preventDefault();
        } else if (minWithdrawal > maxWithdrawal) {
            alert('Minimum withdrawal amount cannot be greater than maximum withdrawal amount.');
            event.preventDefault();
        }
    });
});
</script>