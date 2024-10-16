<?php
// Fetch pending deposits
$stmt = $pdo->query("SELECT * FROM deposits WHERE status = 'pending' ORDER BY created_at DESC");
$pendingDeposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deposit actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $depositId = $_POST['deposit_id'];
    $action = $_POST['action'];

    if ($action === 'complete') {
        $stmt = $pdo->prepare("UPDATE deposits SET status = 'completed' WHERE id = ?");
        $stmt->execute([$depositId]);
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE deposits SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$depositId]);
    }

    // Redirect to refresh the page
    header("Location: index.php?action=manage_deposits");
    exit();
}
?>

<h2>Manage Deposits</h2>

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
        <?php foreach ($pendingDeposits as $deposit): ?>
        <tr>
            <td><?php echo htmlspecialchars($deposit['id']); ?></td>
            <td><?php echo htmlspecialchars($deposit['user_id']); ?></td>
            <td><?php echo htmlspecialchars($deposit['amount']); ?></td>
            <td><?php echo htmlspecialchars($deposit['method']); ?></td>
            <td><?php echo htmlspecialchars($deposit['created_at']); ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="deposit_id" value="<?php echo $deposit['id']; ?>">
                    <button type="submit" name="action" value="complete">Complete</button>
                    <button type="submit" name="action" value="reject">Reject</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>