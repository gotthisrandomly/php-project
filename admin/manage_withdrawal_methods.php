<?php
// Fetch withdrawal methods
$stmt = $pdo->query("SELECT * FROM withdrawal_methods ORDER BY name");
$withdrawalMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle withdrawal method actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'create' || $action === 'edit') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $enabled = isset($_POST['enabled']) ? 1 : 0;

        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO withdrawal_methods (name, description, enabled) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $enabled]);
        } else {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("UPDATE withdrawal_methods SET name = ?, description = ?, enabled = ? WHERE id = ?");
            $stmt->execute([$name, $description, $enabled, $id]);
        }
    } elseif ($action === 'toggle') {
        $id = $_POST['id'];
        $enabled = $_POST['enabled'] === '1' ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE withdrawal_methods SET enabled = ? WHERE id = ?");
        $stmt->execute([$enabled, $id]);
    }

    // Redirect to refresh the page
    header("Location: index.php?action=manage_withdrawal_methods");
    exit();
}
?>

<h2>Manage Withdrawal Methods</h2>

<h3>Create New Withdrawal Method</h3>
<form method="POST">
    <input type="hidden" name="action" value="create">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>
    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea>
    <label for="enabled">Enabled:</label>
    <input type="checkbox" id="enabled" name="enabled" value="1" checked>
    <button type="submit">Create</button>
</form>

<h3>Existing Withdrawal Methods</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Enabled</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($withdrawalMethods as $method): ?>
        <tr>
            <td><?php echo htmlspecialchars($method['id']); ?></td>
            <td><?php echo htmlspecialchars($method['name']); ?></td>
            <td><?php echo htmlspecialchars($method['description']); ?></td>
            <td><?php echo $method['enabled'] ? 'Yes' : 'No'; ?></td>
            <td>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
                    <input type="hidden" name="enabled" value="<?php echo $method['enabled']; ?>">
                    <button type="submit"><?php echo $method['enabled'] ? 'Disable' : 'Enable'; ?></button>
                </form>
                <button onclick="editMethod(<?php echo htmlspecialchars(json_encode($method)); ?>)">Edit</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="editModal" style="display: none;">
    <h3>Edit Withdrawal Method</h3>
    <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="edit_id" name="id">
        <label for="edit_name">Name:</label>
        <input type="text" id="edit_name" name="name" required>
        <label for="edit_description">Description:</label>
        <textarea id="edit_description" name="description" required></textarea>
        <label for="edit_enabled">Enabled:</label>
        <input type="checkbox" id="edit_enabled" name="enabled" value="1">
        <button type="submit">Update</button>
        <button type="button" onclick="closeEditModal()">Cancel</button>
    </form>
</div>

<script>
function editMethod(method) {
    document.getElementById('edit_id').value = method.id;
    document.getElementById('edit_name').value = method.name;
    document.getElementById('edit_description').value = method.description;
    document.getElementById('edit_enabled').checked = method.enabled === 1;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>