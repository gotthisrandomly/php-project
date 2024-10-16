<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update slot machine settings
    $settings = [
        'min_bet' => floatval($_POST['min_bet']),
        'max_bet' => floatval($_POST['max_bet']),
        'default_bet' => floatval($_POST['default_bet']),
        'default_lines' => intval($_POST['default_lines']),
        'wild_symbol' => $_POST['wild_symbol'],
        'scatter_symbol' => $_POST['scatter_symbol']
    ];

    // Update settings in the database
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET value = :value WHERE name = :name");
        $stmt->execute(['value' => $value, 'name' => $key]);
    }

    $success_message = "Settings updated successfully!";
}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM settings");
$current_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slot Machine Settings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Slot Machine Settings</h1>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="min_bet">Minimum Bet</label>
                <input type="number" class="form-control" id="min_bet" name="min_bet" step="0.01" value="<?php echo $current_settings['min_bet']; ?>" required>
            </div>
            <div class="form-group">
                <label for="max_bet">Maximum Bet</label>
                <input type="number" class="form-control" id="max_bet" name="max_bet" step="0.01" value="<?php echo $current_settings['max_bet']; ?>" required>
            </div>
            <div class="form-group">
                <label for="default_bet">Default Bet</label>
                <input type="number" class="form-control" id="default_bet" name="default_bet" step="0.01" value="<?php echo $current_settings['default_bet']; ?>" required>
            </div>
            <div class="form-group">
                <label for="default_lines">Default Number of Lines</label>
                <input type="number" class="form-control" id="default_lines" name="default_lines" min="1" max="20" value="<?php echo $current_settings['default_lines']; ?>" required>
            </div>
            <div class="form-group">
                <label for="wild_symbol">Wild Symbol</label>
                <input type="text" class="form-control" id="wild_symbol" name="wild_symbol" value="<?php echo $current_settings['wild_symbol']; ?>" required>
            </div>
            <div class="form-group">
                <label for="scatter_symbol">Scatter Symbol</label>
                <input type="text" class="form-control" id="scatter_symbol" name="scatter_symbol" value="<?php echo $current_settings['scatter_symbol']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>