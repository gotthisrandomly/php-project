<?php
function playRoulette($user_id, $bet_amount, $bet_type, $bet_value) {
    $result = spinRoulette();
    $win_amount = calculateRouletteWin($bet_amount, $bet_type, $bet_value, $result);
    
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

function spinRoulette() {
    return rand(0, 36);
}

function calculateRouletteWin($bet_amount, $bet_type, $bet_value, $result) {
    switch ($bet_type) {
        case 'number':
            return ($bet_value == $result) ? $bet_amount * 35 : 0;
        case 'color':
            $winning_color = ($result == 0) ? 'green' : (($result % 2 == 0) ? 'black' : 'red');
            return ($bet_value == $winning_color) ? $bet_amount : 0;
        case 'even_odd':
            if ($result == 0) return 0;
            $winning_type = ($result % 2 == 0) ? 'even' : 'odd';
            return ($bet_value == $winning_type) ? $bet_amount : 0;
        default:
            return 0;
    }
}