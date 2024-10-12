<?php include 'header.php'; ?>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>
    <a href="/admin/logout" class="btn btn-danger mb-3">Logout</a>

    <h3>Player Accounts</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($players as $player): ?>
                <tr>
                    <td><?php echo htmlspecialchars($player['id']); ?></td>
                    <td><?php echo htmlspecialchars($player['username']); ?></td>
                    <td>$<?php echo number_format($player['balance'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Pending Cashouts</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Player ID</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pendingCashouts as $cashout): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cashout['id']); ?></td>
                    <td><?php echo htmlspecialchars($cashout['player_id']); ?></td>
                    <td>$<?php echo number_format($cashout['amount'], 2); ?></td>
                    <td>
                        <form action="/admin/approve-cashout" method="post" style="display: inline;">
                            <input type="hidden" name="cashout_id" value="<?php echo htmlspecialchars($cashout['id']); ?>">
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form action="/admin/block-cashout" method="post" style="display: inline;">
                            <input type="hidden" name="cashout_id" value="<?php echo htmlspecialchars($cashout['id']); ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Block</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>