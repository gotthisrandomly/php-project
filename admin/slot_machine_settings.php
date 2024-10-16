<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

// Check if user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Function to handle POST requests
function handlePost($pdo) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_paylines':
                $paylines = $_POST['paylines'];
                $stmt = $pdo->prepare("UPDATE slot_settings SET value = ? WHERE name = 'paylines'");
                $stmt->execute([$paylines]);
                break;
            case 'update_symbols':
                $symbols = $_POST['symbols'];
                foreach ($symbols as $index => $symbol) {
                    $name = $symbol['name'];
                    $image = $_FILES['symbol_images']['name'][$index];
                    $tmp_name = $_FILES['symbol_images']['tmp_name'][$index];
                    
                    if (!empty($image)) {
                        $target_dir = "../images/symbols/";
                        $target_file = $target_dir . basename($image);
                        move_uploaded_file($tmp_name, $target_file);
                        
                        $stmt = $pdo->prepare("UPDATE slot_symbols SET name = ?, image = ? WHERE id = ?");
                        $stmt->execute([$name, $target_file, $index + 1]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE slot_symbols SET name = ? WHERE id = ?");
                        $stmt->execute([$name, $index + 1]);
                    }
                }
                break;
            case 'update_reels':
                $reels = $_POST['reels'];
                foreach ($reels as $reel_number => $symbols) {
                    $symbols_json = json_encode($symbols);
                    $stmt = $pdo->prepare("UPDATE slot_settings SET value = ? WHERE name = ?");
                    $stmt->execute([$symbols_json, 'reel_' . $reel_number]);
                }
                break;
            case 'update_payouts':
                $payouts = $_POST['payouts'];
                foreach ($payouts as $symbol_id => $symbol_payouts) {
                    foreach ($symbol_payouts as $count => $payout) {
                        $stmt = $pdo->prepare("UPDATE slot_settings SET value = ? WHERE name = ?");
                        $stmt->execute([$payout, 'payout_' . $symbol_id . '_' . $count]);
                    }
                }
                break;
            case 'update_bet_settings':
                $min_bet = $_POST['min_bet'];
                $max_bet = $_POST['max_bet'];
                $bet_increment = $_POST['bet_increment'];
                $default_bet = $_POST['default_bet'];
                $default_lines = $_POST['default_lines'];
                
                $stmt = $pdo->prepare("UPDATE slot_settings SET value = ? WHERE name = 'min_bet'");
                $stmt->execute([$min_bet]);
                $stmt = $pdo->prepare("UPDATE slot_settings SET value = ? WHERE name = 'max_bet'");
                $stmt->execute([$max_bet]);
                $stmt = $pdo->prepare("UPDATE slot_settings SET value = ? WHERE name = 'bet_increment'");
                $stmt->execute([$bet_increment]);
                $stmt = $pdo->prepare("UPDATE slot_settings SET value = ? WHERE name = 'default_bet'");
                $stmt->execute([$default_bet]);
                $stmt = $pdo->prepare("UPDATE slot_settings SET value = ? WHERE name = 'default_lines'");
                $stmt->execute([$default_lines]);
                break;
        }
    }
}

// Handle POST request
handlePost($pdo);

// Fetch current slot machine settings
$stmt = $pdo->query("SELECT * FROM slot_settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch current symbols
$stmt = $pdo->query("SELECT * FROM slot_symbols ORDER BY id");
$symbols = $stmt->fetchAll();

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
    <div class="container">
        <h1 class="mt-4">Slot Machine Settings</h1>
        
        <form method="post" enctype="multipart/form-data">
            <h2 class="mt-4">Paylines</h2>
            <div class="form-group">
                <label for="paylines">Number of Paylines</label>
                <input type="number" class="form-control" id="paylines" name="paylines" value="<?php echo htmlspecialchars($settings['paylines']); ?>" min="1" max="20">
            </div>
            <button type="submit" class="btn btn-primary" name="action" value="update_paylines">Update Paylines</button>
            
            <h2 class="mt-4">Symbols</h2>
            <?php foreach ($symbols as $index => $symbol): ?>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="symbol_name_<?php echo $index; ?>">Symbol Name</label>
                    <input type="text" class="form-control" id="symbol_name_<?php echo $index; ?>" name="symbols[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($symbol['name']); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="symbol_image_<?php echo $index; ?>">Symbol Image</label>
                    <input type="file" class="form-control-file" id="symbol_image_<?php echo $index; ?>" name="symbol_images[]">
                    <?php if (!empty($symbol['image'])): ?>
                    <img src="<?php echo htmlspecialchars($symbol['image']); ?>" alt="<?php echo htmlspecialchars($symbol['name']); ?>" class="mt-2" style="max-width: 50px;">
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary" name="action" value="update_symbols">Update Symbols</button>
            
            <h2 class="mt-4">Reels Configuration</h2>
            <?php for ($reel = 1; $reel <= 5; $reel++): ?>
            <h3>Reel <?php echo $reel; ?></h3>
            <div class="form-group">
                <label for="reel_<?php echo $reel; ?>">Select symbols for Reel <?php echo $reel; ?></label>
                <select multiple class="form-control" id="reel_<?php echo $reel; ?>" name="reels[<?php echo $reel; ?>][]">
                    <?php foreach ($symbols as $symbol): ?>
                    <option value="<?php echo $symbol['id']; ?>"><?php echo htmlspecialchars($symbol['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endfor; ?>
            <button type="submit" class="btn btn-primary" name="action" value="update_reels">Update Reels</button>
            
            <h2 class="mt-4">Payout Configuration</h2>
            <?php foreach ($symbols as $symbol): ?>
            <h3><?php echo htmlspecialchars($symbol['name']); ?> Payouts</h3>
            <?php for ($count = 3; $count <= 5; $count++): ?>
            <div class="form-group">
                <label for="payout_<?php echo $symbol['id']; ?>_<?php echo $count; ?>">Payout for <?php echo $count; ?> <?php echo htmlspecialchars($symbol['name']); ?> symbols</label>
                <input type="number" class="form-control" id="payout_<?php echo $symbol['id']; ?>_<?php echo $count; ?>" name="payouts[<?php echo $symbol['id']; ?>][<?php echo $count; ?>]" value="<?php echo htmlspecialchars($settings['payout_' . $symbol['id'] . '_' . $count] ?? ''); ?>" step="0.01">
            </div>
            <?php endfor; ?>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary" name="action" value="update_payouts">Update Payouts</button>
            
            <h2 class="mt-4">Bet Settings</h2>
            <div class="form-group">
                <label for="min_bet">Minimum Bet</label>
                <input type="number" class="form-control" id="min_bet" name="min_bet" value="<?php echo htmlspecialchars($settings['min_bet']); ?>" step="0.01">
            </div>
            <div class="form-group">
                <label for="max_bet">Maximum Bet</label>
                <input type="number" class="form-control" id="max_bet" name="max_bet" value="<?php echo htmlspecialchars($settings['max_bet']); ?>" step="0.01">
            </div>
            <div class="form-group">
                <label for="bet_increment">Bet Increment</label>
                <input type="number" class="form-control" id="bet_increment" name="bet_increment" value="<?php echo htmlspecialchars($settings['bet_increment']); ?>" step="0.01">
            </div>
            <div class="form-group">
                <label for="default_bet">Default Bet</label>
                <input type="number" class="form-control" id="default_bet" name="default_bet" value="<?php echo htmlspecialchars($settings['default_bet']); ?>" step="0.01">
            </div>
            <div class="form-group">
                <label for="default_lines">Default Number of Lines</label>
                <input type="number" class="form-control" id="default_lines" name="default_lines" value="<?php echo htmlspecialchars($settings['default_lines']); ?>" min="1" max="20">
            </div>
            <button type="submit" class="btn btn-primary" name="action" value="update_bet_settings">Update Bet Settings</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>