<?php
// Fetch pending withdrawals
$stmt = $pdo->query("SELECT * FROM withdrawals WHERE status = 'pending' ORDER BY created_at DESC");
$pendingWithdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle withdrawal actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $withdrawalId = $_POST['withdrawal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'approved' WHERE id = ?");
        $stmt->execute([$withdrawalId]);
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$withdrawalId]);
    } elseif ($action === 'complete') {
        $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'completed' WHERE id = ?");
        $stmt->execute([$withdrawalId]);
    }

    // Redirect to refresh the page
    header("Location: index.php?action=manage_withdrawals");
    exit();
}
?>

<h2>Manage Withdrawals</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pendingWithdrawals as $withdrawal): ?>
        <tr>
            <td><?php echo htmlspecialchars($withdrawal['id']); ?></td>
            <td><?php echo htmlspecialchars($withdrawal['user_id']); ?></td>
            <td><?php echo htmlspecialchars($withdrawal['amount']); ?></td>
            <td><?php echo htmlspecialchars($withdrawal['method']); ?></td>
            <td><?php echo htmlspecialchars($withdrawal['created_at']); ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                    <button type="submit" name="action" value="approve">Approve</button>
                    <button type="submit" name="action" value="reject">Reject</button>
                    <button type="submit" name="action" value="complete">Complete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>