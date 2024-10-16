<?php
// Fetch payment gateways
$stmt = $pdo->query("SELECT * FROM payment_gateways ORDER BY name");
$paymentGateways = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle payment gateway actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gatewayId = $_POST['gateway_id'];
    $referenceCurrency = $_POST['reference_currency'];
    $rate = $_POST['rate'];

    $stmt = $pdo->prepare("UPDATE payment_gateways SET reference_currency = ?, rate = ? WHERE id = ?");
    $stmt->execute([$referenceCurrency, $rate, $gatewayId]);

    // Redirect to refresh the page
    header("Location: index.php?action=manage_payment_gateways");
    exit();
}
?>

<h2>Manage Payment Gateways</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Reference Currency</th>
            <th>Rate</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($paymentGateways as $gateway): ?>
        <tr>
            <td><?php echo htmlspecialchars($gateway['id']); ?></td>
            <td><?php echo htmlspecialchars($gateway['name']); ?></td>
            <td><?php echo htmlspecialchars($gateway['reference_currency']); ?></td>
            <td><?php echo htmlspecialchars($gateway['rate']); ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="gateway_id" value="<?php echo $gateway['id']; ?>">
                    <input type="text" name="reference_currency" value="<?php echo htmlspecialchars($gateway['reference_currency']); ?>" required>
                    <input type="number" name="rate" value="<?php echo htmlspecialchars($gateway['rate']); ?>" step="0.0001" required>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>