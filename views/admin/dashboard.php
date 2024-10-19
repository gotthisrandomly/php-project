<?php include 'views/admin/header.php'; ?>

<h1>Admin Dashboard</h1>

<h2>Player Accounts</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Balance</th>
    </tr>
    <?php foreach ($playerAccounts as $player): ?>
    <tr>
        <td><?= $player['id'] ?></td>
        <td><?= $player['username'] ?></td>
        <td><?= $player['email'] ?></td>
        <td><?= $player['balance'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Pending Cashouts</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Player ID</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($pendingCashouts as $cashout): ?>
    <tr>
        <td><?= $cashout['id'] ?></td>
        <td><?= $cashout['player_id'] ?></td>
        <td><?= $cashout['amount'] ?></td>
        <td><?= $cashout['status'] ?></td>
        <td>
            <a href="/admin/approve-cashout/<?= $cashout['id'] ?>">Approve</a>
            <a href="/admin/block-cashout/<?= $cashout['id'] ?>">Block</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Game Settings</h2>
<table>
    <tr>
        <th>Setting</th>
        <th>Value</th>
        <th>Action</th>
    </tr>
    <?php foreach ($gameSettings as $setting): ?>
    <tr>
        <td><?= $setting['name'] ?></td>
        <td><?= $setting['value'] ?></td>
        <td><a href="/admin/edit-setting/<?= $setting['name'] ?>">Edit</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include 'views/admin/footer.php'; ?>