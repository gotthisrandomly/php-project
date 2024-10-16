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

// Fetch current jackpot value
$stmt = $pdo->query("SELECT value FROM jackpot WHERE id = 1");
$jackpot = $stmt->fetchColumn();

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
}

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

// Handle setting max bet limit
if (isset($_POST['action']) && $_POST['action'] === 'set_max_bet_limit') {
    $max_bet_limit = floatval($_POST['max_bet_limit']);
    if ($max_bet_limit >= $settings['min_bet'] && $max_bet_limit <= $settings['max_bet']) {
        $_SESSION['max_bet_limit'] = $max_bet_limit;
        echo json_encode(['success' => true, 'message' => 'Maximum bet limit set successfully.']);
    } else {
        echo json_encode(['error' => 'Invalid maximum bet limit.']);
    }
    exit;
}

// Handle spin action
if (isset($_POST['action']) && $_POST['action'] === 'spin') {
    $bet_amount = floatval($_POST['bet_amount']);
    $lines = intval($_POST['lines']);
    $client_seed = $_POST['client_seed'] ?? generateClientSeed();
    $free_spins = isset($_SESSION['free_spins']) ? $_SESSION['free_spins'] : 0;
    
    // Validate bet amount and lines
    if ($free_spins == 0 && ($bet_amount < $settings['min_bet'] || $bet_amount > $settings['max_bet'] || $lines < 1 || $lines > $settings['paylines'])) {
        echo json_encode(['error' => "Invalid bet amount or number of lines."]);
        exit;
    }

    // Deduct bet amount from user's balance if it's not a free spin
    $total_bet = $free_spins > 0 ? 0 : $bet_amount * $lines;
    if ($free_spins == 0) {
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$total_bet, $_SESSION['user_id']]);
    }
    
    // Generate server seed and slot result
    $server_seed = generateServerSeed();
    $result = generateSlotResult($pdo, $settings, $server_seed, $client_seed);
    
    // Calculate winnings
    $win_data = checkWinningCombinations($result, $settings, $symbols, $lines, $bet_amount);
    $winnings = $win_data['total_win'];
    $winning_lines = $win_data['winning_lines'];
    
    // Check for jackpot win
    $jackpot_won = false;
    if (count(array_unique($result)) === 1 && $result[0] == $settings['jackpot_symbol']) {
        $jackpot_won = true;
        $winnings += $jackpot;
        
        // Reset jackpot
        $stmt = $pdo->prepare("UPDATE jackpot SET value = ? WHERE id = 1");
        $stmt->execute([$settings['jackpot_seed']]);
    } else {
        // Increment jackpot
        $jackpot_increment = $total_bet * $settings['jackpot_contribution'];
        $stmt = $pdo->prepare("UPDATE jackpot SET value = value + ? WHERE id = 1");
        $stmt->execute([$jackpot_increment]);
    }
    
    // Check for free spins trigger
    $scatter_count = array_count_values($result)[$settings['scatter_symbol']] ?? 0;
    $free_spins_won = 0;
    if ($scatter_count >= 3) {
        $free_spins_won = $settings['free_spins_' . $scatter_count];
        $free_spins += $free_spins_won;
    }

    // Update free spins count
    if ($free_spins > 0) {
        $free_spins--;
    }
    $_SESSION['free_spins'] = $free_spins;
    
    // Update user's balance with winnings
    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$winnings, $_SESSION['user_id']]);
    
    // Store game result in database
    $stmt = $pdo->prepare("INSERT INTO games (user_id, game_type, bet_amount, win_amount, result, server_seed, client_seed, free_spin, jackpot_won) VALUES (?, 'slot_machine', ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total_bet, $winnings, json_encode($result), $server_seed, $client_seed, $free_spins > 0 ? 1 : 0, $jackpot_won ? 1 : 0]);
    
    // Store the server seed hash for the next game
    $_SESSION['next_server_seed_hash'] = hash('sha256', $server_seed);

    // Fetch updated balance and jackpot
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $updated_balance = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT value FROM jackpot WHERE id = 1");
    $updated_jackpot = $stmt->fetchColumn();

    // Prepare and send JSON response
    $response = [
        'symbols' => array_map(function($symbol_id) use ($symbols) {
            return $symbols[$symbol_id - 1]['name'];
        }, $result),
        'winnings' => $winnings,
        'winning_lines' => $winning_lines,
        'balance' => $updated_balance,
        'next_server_seed_hash' => $_SESSION['next_server_seed_hash'],
        'free_spins_won' => $free_spins_won,
        'free_spins_left' => $free_spins,
        'bet_amount' => $total_bet,
        'jackpot' => $updated_jackpot,
        'jackpot_won' => $jackpot_won
    ];

    echo json_encode($response);
    exit;
}
    echo json_encode($response);
    exit;
}

// Generate a new server seed hash if it doesn't exist
if (!isset($_SESSION['next_server_seed_hash'])) {
    $_SESSION['next_server_seed_hash'] = hash('sha256', generateServerSeed());
}

// Fetch user's current balance
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_balance = $stmt->fetchColumn();

// Render HTML only if it's not an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
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
                height: 300px;
                border: 2px solid #000;
                display: flex;
                flex-direction: column;
                justify-content: space-around;
                align-items: center;
                font-size: 24px;
                overflow: hidden;
            }
            .symbol {
                width: 80px;
                height: 80px;
                display: flex;
                justify-content: center;
                align-items: center;
                border: 1px solid #ccc;
            }
            .winning-line {
                background-color: rgba(255, 255, 0, 0.3);
            }
            @keyframes spin {
                0% { transform: translateY(0); }
                100% { transform: translateY(-300px); }
            }
            .spinning {
                animation: spin 0.5s linear infinite;
            }
        </style>
    </head>
        <div class="container">
            <h1 class="mt-4">Slot Machine Game</h1>
            <div class="jackpot-display">
                <h2>Current Jackpot: $<span id="jackpot-value"><?php echo number_format($jackpot, 2); ?></span></h2>
            </div>
            <div class="slot-machine">
                <?php for ($i = 0; $i < 5; $i++): ?>
                <div class="reel" id="reel<?php echo $i; ?>">
                    <div class="symbols-container">
                        <?php for ($j = 0; $j < 3; $j++): ?>
                        <div class="symbol" id="symbol<?php echo $i; ?>-<?php echo $j; ?>">?</div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            
            <form method="post" id="spin-form">
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
                <div class="form-group">
                    <label for="auto_spins">Auto Spins</label>
                    <input type="number" class="form-control" id="auto_spins" name="auto_spins" value="0" min="0" max="100">
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="stop_on_win" name="stop_on_win">
                    <label class="form-check-label" for="stop_on_win">Stop on Win</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="stop_on_feature" name="stop_on_feature">
                    <label class="form-check-label" for="stop_on_feature">Stop on Feature (Free Spins)</label>
                </div>
                <button type="submit" class="btn btn-primary" id="spin-button" name="action" value="spin">Spin</button>
                <button type="button" class="btn btn-secondary" id="auto-spin">Auto Spin</button>
            </form>
            
            <div class="mt-3">
                <strong>Next Server Seed Hash:</strong> <span id="next-server-seed-hash"><?php echo $_SESSION['next_server_seed_hash']; ?></span>
            </div>
            
            <div class="mt-3">
                <strong>Current Balance:</strong> $<span class="balance"><?php echo number_format($user_balance, 2); ?></span>
            </div>
            
            <div class="mt-3">
                <strong>Current Bet:</strong> $<span id="bet-amount-display"><?php echo number_format($settings['default_bet'], 2); ?></span>
            </div>
            
            <div class="mt-3">
                <strong>Winnings:</strong> $<span class="winnings">0.00</span>
            </div>

            <div class="mt-3">
                <strong>Free Spins Left:</strong> <span id="free-spins-left">0</span>
            </div>

            <div class="mt-3">
                <h3>Winning Lines</h3>
                <div id="winning-lines"></div>
            </div>

            <div class="mt-5">
                <h3>Paytable</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Symbol</th>
                            <th>3 of a kind</th>
                            <th>4 of a kind</th>
                            <th>5 of a kind</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($symbols as $symbol): ?>
                        <tr>
                            <td><?php echo $symbol['name']; ?></td>
                            <td><?php echo $settings['payout_' . $symbol['id'] . '_3']; ?>x</td>
                            <td><?php echo $settings['payout_' . $symbol['id'] . '_4']; ?>x</td>
                            <td><?php echo $settings['payout_' . $symbol['id'] . '_5']; ?>x</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                <h3>Recent Spins</h3>
                <table class="table table-striped" id="spin-history">
                    <thead>
                        <tr>
                            <th>Spin</th>
                            <th>Bet</th>
                            <th>Win</th>
                            <th>Symbols</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                <h3>Responsible Gaming</h3>
                <form id="responsible-gaming-form">
                    <div class="form-group">
                        <label for="max-bet-limit">Set Maximum Bet Limit</label>
                        <input type="number" class="form-control" id="max-bet-limit" name="max_bet_limit" min="<?php echo $settings['min_bet']; ?>" max="<?php echo $settings['max_bet']; ?>" step="0.01">
                    </div>
                    <button type="submit" class="btn btn-primary">Set Limit</button>
                </form>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script>
            $(document).ready(function() {
                var freeSpinsLeft = 0;
                var spinSound = new Audio('spin.mp3');
                var winSound = new Audio('win.mp3');
                var jackpotSound = new Audio('jackpot.mp3');

                function spin() {
                    // Start spinning animation and play sound
                    $('.symbols-container').addClass('spinning');
                    spinSound.play();

                    $.ajax({
                        url: 'slot_machine.php',
                        method: 'POST',
                        data: $('#spin-form').serialize() + '&action=spin',
                        success: function(response) {
                            var result = JSON.parse(response);
                            if (result.error) {
                                alert(result.error);
                                stopSpinning();
                            } else {
                                setTimeout(function() {
                                    stopSpinning();
                                    updateSlotMachine(result.symbols);
                                    updateBalance(result.balance);
                                    updateWinnings(result.winnings);
                                    updateNextServerSeedHash(result.next_server_seed_hash);
                                    showWinningLines(result.winning_lines);
                                    handleFreeSpins(result.free_spins_won, result.free_spins_left);
                                    updateBetAmount(result.bet_amount);
                                    updateJackpot(result.jackpot);

                                    if (result.jackpot_won) {
                                        jackpotSound.play();
                                        alert('Congratulations! You won the jackpot!');
                                    } else if (result.winnings > 0) {
                                        winSound.play();
                                    }
                                }, 1500); // Stop spinning after 1.5 seconds
                            }
                        }
                    });
                }

                function stopSpinning() {
                    $('.symbols-container').removeClass('spinning');
                    spinSound.pause();
                    spinSound.currentTime = 0;
                }

                $('#spin-form').submit(function(e) {
                    e.preventDefault();
                    spin();
                });

                $('#auto-spin').click(function() {
                    var spinsLeft = parseInt($('#auto_spins').val());
                    var stopOnWin = $('#stop_on_win').is(':checked');
                    var stopOnFeature = $('#stop_on_feature').is(':checked');

                    function autoSpin() {
                        spin();
                        spinsLeft--;

                        if (spinsLeft > 0 && (!stopOnWin || parseFloat($('.winnings').text()) === 0) && (!stopOnFeature || freeSpinsLeft === 0)) {
                            setTimeout(autoSpin, 2500); // Wait 2.5 seconds before next spin
                        }
                    }

                    autoSpin();
                });

                function updateSlotMachine(symbols) {
                    symbols.forEach((symbol, index) => {
                        for (let i = 0; i < 3; i++) {
                            $(`#symbol${index}-${i}`).text(symbol).removeClass('winning-line');
                        }
                    });
                }

                function updateBalance(balance) {
                    $('.balance').text(parseFloat(balance).toFixed(2));
                }

                function updateWinnings(winnings) {
                    $('.winnings').text(parseFloat(winnings).toFixed(2));
                }

                function updateNextServerSeedHash(hash) {
                    $('#next-server-seed-hash').text(hash);
                }

                function showWinningLines(winningLines) {
                    $('#winning-lines').empty();
                    winningLines.forEach((line) => {
                        $('#winning-lines').append(`<p>Line ${line.line}: ${line.symbols.join(' - ')} (Payout: $${line.payout.toFixed(2)})</p>`);
                        line.symbols.forEach((symbol, index) => {
                            $(`#symbol${index}-${line.line - 1}`).addClass('winning-line');
                        });
                    });
                }

                function handleFreeSpins(freeSpinsWon, freeSpinsLeft) {
                    if (freeSpinsWon > 0) {
                        alert(`Congratulations! You've won ${freeSpinsWon} free spins!`);
                    }
                    
                    this.freeSpinsLeft = freeSpinsLeft;
                    updateFreeSpinsDisplay();
                function updateJackpot(jackpotValue) {
                    $('#jackpot-value').text(parseFloat(jackpotValue).toFixed(2));
                }

                var spinCount = 0;
                var spinHistory = [];

                function updateSpinHistory(bet, win, symbols) {
                    spinCount++;
                    var historyEntry = {
                        spin: spinCount,
                        bet: bet,
                        win: win,
                        symbols: symbols
                    };
                    spinHistory.unshift(historyEntry);
                    if (spinHistory.length > 10) {
                        spinHistory.pop();
                    }
                    displaySpinHistory();
                }

                function displaySpinHistory() {
                    var $historyBody = $('#spin-history tbody');
                    $historyBody.empty();
                    spinHistory.forEach(function(entry) {
                        var row = `<tr>
                            <td>${entry.spin}</td>
                            <td>$${entry.bet.toFixed(2)}</td>
                            <td>$${entry.win.toFixed(2)}</td>
                            <td>${entry.symbols.join(', ')}</td>
                        </tr>`;
                        $historyBody.append(row);
                    });
                }

                // Update the spin function to call updateSpinHistory
                var originalSpin = spin;
                spin = function() {
                    originalSpin();
                    $.ajax({
                        url: 'slot_machine.php',
                        method: 'POST',
                        data: $('#spin-form').serialize() + '&action=spin',
                        success: function(response) {
                            var result = JSON.parse(response);
                            if (!result.error) {
                                updateSpinHistory(result.bet_amount, result.winnings, result.symbols);
                            }
                        }
                };
            });
        </script>
    </body>
    </html>
    <?php
}