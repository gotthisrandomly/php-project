<?php include 'views/admin/header.php'; ?>

<h1>Manage Game Settings</h1>

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
        <td>
            <form action="/admin/update-setting" method="post">
                <input type="hidden" name="setting_name" value="<?= $setting['name'] ?>">
                <input type="text" name="setting_value" value="<?= $setting['value'] ?>">
                <input type="submit" value="Update">
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include 'views/admin/footer.php'; ?>