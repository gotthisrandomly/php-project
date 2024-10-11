<?php
function playSlotMachine($user_id, $bet_amount) {
    $symbols = ['🍒', '🍋', '🍊', '🍇', '🔔', '💎', '7️⃣'];
    $reels = 3;
    $result = [];
    
    // Spin the reels
    for ($i = 0; $i < $reels; $i++) {
        $result[] = $symbols[array_rand($symbols)];
    }
    
    // Calculate winnings
    $win_amount = calculateSlotMachineWin($result, $bet_amount);
    
    // Update user balance and record transaction
    if ($win_amount > 0) {
        update_balance($user_id, $win_amount);
        record_transaction($user_id, $win_amount, 'win');
    } else {
        update_balance($user_id, -$bet_amount);
        record_transaction($user_id, $bet_amount, 'bet');
    }
    
    return [
        'result' => $result,
        'win_amount' => $win_amount
    ];
}

function calculateSlotMachineWin($result, $bet_amount) {
    if (count(array_unique($result)) === 1) {
        // All symbols are the same
        switch ($result[0]) {
            case '7️⃣':
                return $bet_amount * 10; // Jackpot
            case '💎':
                return $bet_amount * 7;
            case '🔔':
                return $bet_amount * 5;
            default:
                return $bet_amount * 3;
        }
    } elseif (count(array_unique($result)) === 2) {
        // Two symbols are the same
        return $bet_amount * 2;
    }
    return 0; // No win
}