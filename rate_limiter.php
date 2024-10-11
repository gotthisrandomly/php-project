<?php
function check_rate_limit($user_id, $action, $limit, $time_window) {
    global $db;
    
    $current_time = time();
    $start_time = $current_time - $time_window;
    
    $query = "SELECT COUNT(*) FROM rate_limit_log WHERE user_id = $1 AND action = $2 AND timestamp > to_timestamp($3)";
    $result = pg_query_params($db, $query, array($user_id, $action, $start_time));
    $count = pg_fetch_result($result, 0, 0);
    
    if ($count >= $limit) {
        return false;
    }
    
    $insert_query = "INSERT INTO rate_limit_log (user_id, action, timestamp) VALUES ($1, $2, to_timestamp($3))";
    pg_query_params($db, $insert_query, array($user_id, $action, $current_time));
    
    return true;
}

function is_rate_limited($user_id, $action) {
    switch ($action) {
        case 'slot_machine':
            return !check_rate_limit($user_id, $action, 10, 60); // 10 spins per minute
        case 'roulette':
            return !check_rate_limit($user_id, $action, 5, 60); // 5 spins per minute
        default:
            return false;
    }
}