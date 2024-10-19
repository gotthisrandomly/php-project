<?php include 'views/admin/header.php'; ?>

<h1>Manage Players</h1>

<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Balance</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($playerAccounts as $player): ?>
    <tr>
        <td><?= $player['id'] ?></td>
        <td><?= $player['username'] ?></td>
        <td><?= $player['email'] ?></td>
        <td><?= $player['balance'] ?></td>
        <td>
            <a href="/admin/edit-player/<?= $player['id'] ?>">Edit</a>
            <a href="/admin/ban-player/<?= $player['id'] ?>">Ban</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include 'views/admin/footer.php'; ?>