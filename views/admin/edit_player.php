<?php include 'views/admin/header.php'; ?>

<h1>Edit Player</h1>

<form action="/admin/edit-player/<?php echo $player['id']; ?>" method="post">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo $player['username']; ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $player['email']; ?>" required>
    </div>
    <div class="form-group">
        <label for="balance">Balance:</label>
        <input type="number" id="balance" name="balance" value="<?php echo $player['balance']; ?>" step="0.01" required>
    </div>
    <button type="submit">Update Player</button>
</form>

<h2>Transaction History</h2>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($transactionHistory as $transaction): ?>
        <tr>
            <td><?php echo $transaction['timestamp']; ?></td>
            <td><?php echo $transaction['type']; ?></td>
            <td><?php echo $transaction['amount']; ?></td>
            <td><?php echo $transaction['description']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'views/admin/footer.php'; ?>