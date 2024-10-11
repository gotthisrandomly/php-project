<?php
function get_all_users() {
    global $db;
    $query = "SELECT id, username, email, balance, created_at FROM users ORDER BY id";
    $result = pg_query($db, $query);
    return pg_fetch_all($result);
}

function update_user_balance($user_id, $new_balance) {
    global $db;
    $query = "UPDATE users SET balance = $1 WHERE id = $2";
    return pg_query_params($db, $query, array($new_balance, $user_id));
}

function get_game_settings() {
    global $db;
    $query = "SELECT * FROM game_settings";
    $result = pg_query($db, $query);
    return pg_fetch_assoc($result);
}

function update_game_settings($slot_machine_max_bet, $roulette_max_bet) {
    global $db;
    $query = "UPDATE game_settings SET slot_machine_max_bet = $1, roulette_max_bet = $2";
    return pg_query_params($db, $query, array($slot_machine_max_bet, $roulette_max_bet));
}

function get_recent_transactions($limit = 50) {
    global $db;
    $query = "SELECT * FROM transactions ORDER BY timestamp DESC LIMIT $1";
    $result = pg_query_params($db, $query, array($limit));
    return pg_fetch_all($result);
}