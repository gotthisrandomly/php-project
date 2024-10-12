<?php

function create_deck() {
    $suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
    $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];
    $deck = [];

    foreach ($suits as $suit) {
        foreach ($values as $value) {
            $deck[] = ['suit' => $suit, 'value' => $value];
        }
    }

    shuffle($deck);
    return $deck;
}

function calculate_hand_value($hand) {
    $value = 0;
    $aces = 0;

    foreach ($hand as $card) {
        if ($card['value'] == 'A') {
            $aces++;
            $value += 11;
        } elseif (in_array($card['value'], ['K', 'Q', 'J'])) {
            $value += 10;
        } else {
            $value += intval($card['value']);
        }
    }

    while ($value > 21 && $aces > 0) {
        $value -= 10;
        $aces--;
    }

    return $value;
}

function play_blackjack($user_id, $bet_amount) {
    global $db;

    $deck = create_deck();
    $player_hand = [array_pop($deck), array_pop($deck)];
    $dealer_hand = [array_pop($deck), array_pop($deck)];

    $player_value = calculate_hand_value($player_hand);
    $dealer_value = calculate_hand_value($dealer_hand);

    // Check for natural blackjack
    if ($player_value == 21 && $dealer_value != 21) {
        $winnings = $bet_amount * 2.5;
        update_user_balance($user_id, $winnings);
        record_transaction($user_id, $winnings, 'blackjack_win');
        return [
            'result' => 'win',
            'player_hand' => $player_hand,
            'dealer_hand' => $dealer_hand,
            'winnings' => $winnings,
            'message' => 'Blackjack! You win!'
        ];
    }

    // Player's turn
    while ($player_value < 21) {
        // In a real game, we'd ask the player if they want to hit or stand
        // For simplicity, we'll make the player hit if their hand value is less than 17
        if ($player_value < 17) {
            $player_hand[] = array_pop($deck);
            $player_value = calculate_hand_value($player_hand);
        } else {
            break;
        }
    }

    if ($player_value > 21) {
        record_transaction($user_id, -$bet_amount, 'blackjack_loss');
        return [
            'result' => 'lose',
            'player_hand' => $player_hand,
            'dealer_hand' => $dealer_hand,
            'winnings' => 0,
            'message' => 'Bust! You lose.'
        ];
    }

    // Dealer's turn
    while ($dealer_value < 17) {
        $dealer_hand[] = array_pop($deck);
        $dealer_value = calculate_hand_value($dealer_hand);
    }

    if ($dealer_value > 21 || $player_value > $dealer_value) {
        $winnings = $bet_amount * 2;
        update_user_balance($user_id, $winnings);
        record_transaction($user_id, $winnings, 'blackjack_win');
        return [
            'result' => 'win',
            'player_hand' => $player_hand,
            'dealer_hand' => $dealer_hand,
            'winnings' => $winnings,
            'message' => 'You win!'
        ];
    } elseif ($player_value == $dealer_value) {
        update_user_balance($user_id, $bet_amount);
        return [
            'result' => 'push',
            'player_hand' => $player_hand,
            'dealer_hand' => $dealer_hand,
            'winnings' => $bet_amount,
            'message' => 'Push. Your bet is returned.'
        ];
    } else {
        record_transaction($user_id, -$bet_amount, 'blackjack_loss');
        return [
            'result' => 'lose',
            'player_hand' => $player_hand,
            'dealer_hand' => $dealer_hand,
            'winnings' => 0,
            'message' => 'Dealer wins. You lose.'
        ];
    }
}

function update_user_balance($user_id, $amount) {
    global $db;
    $query = "UPDATE users SET balance = balance + $1 WHERE id = $2";
    return pg_query_params($db, $query, array($amount, $user_id));
}

function record_transaction($user_id, $amount, $type) {
    global $db;
    $query = "INSERT INTO transactions (user_id, amount, type) VALUES ($1, $2, $3)";
    return pg_query_params($db, $query, array($user_id, $amount, $type));
}