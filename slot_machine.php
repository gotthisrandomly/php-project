<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php';

// Fetch slot machine settings
$stmt = $pdo->query("SELECT * FROM slot_settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch symbols
$stmt = $pdo->query("SELECT * FROM slot_symbols ORDER BY id");
$symbols = $stmt->fetchAll();

// Function to generate a provably fair slot result
function generateSlotResult($pdo, $settings, $server_seed, $client_seed) {
    $result = [];
    $hash = hash_hmac('sha256', $server_seed . $client_seed, 'slot_machine_secret');
    $hash_chars = str_split($hash);
    
    for ($reel = 1; $reel <= 5; $reel++) {
        $reel_symbols = json_decode($settings['reel_' . $reel], true);
        $hash_index = hexdec($hash_chars[$reel - 1]);
        $result[] = $reel_symbols[$hash_index % count($reel_symbols)];
    }
    return $result;
}

// Generate server seed
function generateServerSeed() {
    return bin2hex(random_bytes(16));
}

// Generate client seed (if not provided)
function generateClientSeed() {
    return bin2hex(random_bytes(8));
// Function to check for winning combinations
function checkWinningCombinations($result, $settings, $symbols, $lines, $bet_amount) {
    $winnings = 0;
    $winning_lines = [];

    for ($line = 1; $line <= $lines; $line++) {
        $line_symbols = [];
        for ($reel = 0; $reel < 5; $reel++) {
            $line_symbols[] = $result[$reel];
        }

        $count = 1;
        $first_symbol = $line_symbols[0];
        for ($i = 1; $i < 5; $i++) {
            if ($line_symbols[$i] == $first_symbol) {
                $count++;
            } else {
                break;
            }
        }

        if ($count >= 3) {
            $payout_key = 'payout_' . $first_symbol . '_' . $count;
            $payout = $settings[$payout_key] ?? 0;
            $line_win = $payout * $bet_amount;
            $winnings += $line_win;
            $winning_lines[] = [
                'line' => $line,
                'symbols' => array_slice($line_symbols, 0, $count),
                'payout' => $line_win
            ];
        }
    }

    return [
        'total_win' => $winnings,
        'winning_lines' => $winning_lines
    ];
}

// Handle spin action

// Handle spin action
if (isset($_POST['action']) && $_POST['action'] === 'spin') {
    $bet_amount = floatval($_POST['bet_amount']);
    $lines = intval($_POST['lines']);
    $client_seed = $_POST['client_seed'] ?? generateClientSeed();
    
    // Validate bet amount and lines
    if ($bet_amount < $settings['min_bet'] || $bet_amount > $settings['max_bet'] || $lines < 1 || $lines > $settings['paylines']) {
        $error = "Invalid bet amount or number of lines.";
    } else {
        // Deduct bet amount from user's balance
        $total_bet = $bet_amount * $lines;
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$total_bet, $_SESSION['user_id']]);
        
        // Generate server seed and slot result
        $server_seed = generateServerSeed();
        $result = generateSlotResult($pdo, $settings, $server_seed, $client_seed);
        
        // Calculate winnings
        $win_data = checkWinningCombinations($result, $settings, $symbols, $lines, $bet_amount);
        $winnings = $win_data['total_win'];
        $winning_lines = $win_data['winning_lines'];
        
        // Update user's balance with winnings
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$winnings, $_SESSION['user_id']]);
        
        // Store game result in database
        $stmt = $pdo->prepare("INSERT INTO games (user_id, game_type, bet_amount, win_amount, result, server_seed, client_seed) VALUES (?, 'slot_machine', ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total_bet, $winnings, json_encode($result), $server_seed, $client_seed]);
        
        // Store the server seed hash for the next game
        $_SESSION['next_server_seed_hash'] = hash('sha256', $server_seed);
    }
}
}

// Generate a new server seed hash if it doesn't exist
if (!isset($_SESSION['next_server_seed_hash'])) {
    $_SESSION['next_server_seed_hash'] = hash('sha256', generateServerSeed());
}

// Fetch user's current balance
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_balance = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slot Machine Game</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .slot-machine {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .reel {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Slot Machine Game</h1>
        
        <div class="slot-machine">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="reel" id="reel<?php echo $i; ?>">
                <?php echo isset($result[$i]) ? $symbols[$result[$i] - 1]['name'] : '?'; ?>
            </div>
            <?php endfor; ?>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="bet_amount">Bet Amount</label>
                <input type="number" class="form-control" id="bet_amount" name="bet_amount" value="<?php echo $settings['default_bet']; ?>" min="<?php echo $settings['min_bet']; ?>" max="<?php echo $settings['max_bet']; ?>" step="<?php echo $settings['bet_increment']; ?>">
            </div>
            <div class="form-group">
                <label for="lines">Number of Lines</label>
                <input type="number" class="form-control" id="lines" name="lines" value="<?php echo $settings['default_lines']; ?>" min="1" max="<?php echo $settings['paylines']; ?>">
            </div>
            <div class="form-group">
                <label for="client_seed">Client Seed (optional)</label>
                <input type="text" class="form-control" id="client_seed" name="client_seed" placeholder="Enter a custom seed or leave blank for a random seed">
            </div>
            <button type="submit" class="btn btn-primary" name="action" value="spin">Spin</button>
        </form>
        
        <div class="mt-3">
            <strong>Next Server Seed Hash:</strong> <?php echo $_SESSION['next_server_seed_hash']; ?>
        </div>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($result)): ?>
        <div class="alert alert-info mt-3">
            Your bet: $<?php echo number_format($total_bet, 2); ?><br>
            Your winnings: $<?php echo number_format($winnings, 2); ?>
        </div>
        <?php endif; ?>
        
        <div class="mt-3">
            Your current balance: $<?php echo number_format($user_balance, 2); ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>