<?php
function set_deposit_limit($user_id, $limit) {
    global $db;
    $query = "UPDATE users SET deposit_limit = $1 WHERE id = $2";
    return pg_query_params($db, $query, array($limit, $user_id));
}

function get_deposit_limit($user_id) {
    global $db;
    $query = "SELECT deposit_limit FROM users WHERE id = $1";
    $result = pg_query_params($db, $query, array($user_id));
    return pg_fetch_result($result, 0, 0);
}

function set_self_exclusion($user_id, $end_date) {
    global $db;
    $query = "UPDATE users SET self_exclusion_end = $1 WHERE id = $2";
    return pg_query_params($db, $query, array($end_date, $user_id));
}

function check_self_exclusion($user_id) {
    global $db;
    $query = "SELECT self_exclusion_end FROM users WHERE id = $1";
    $result = pg_query_params($db, $query, array($user_id));
    $end_date = pg_fetch_result($result, 0, 0);
    
    if ($end_date && strtotime($end_date) > time()) {
        return $end_date;
    }
    return false;
}

function check_deposit_limit($user_id, $amount) {
    $limit = get_deposit_limit($user_id);
    if ($limit === null) {
        return true; // No limit set
    }
    
    global $db;
    $query = "SELECT SUM(amount) FROM transactions WHERE user_id = $1 AND type = 'deposit' AND timestamp > NOW() - INTERVAL '24 hours'";
    $result = pg_query_params($db, $query, array($user_id));
    $total_deposits = pg_fetch_result($result, 0, 0);
    
    return ($total_deposits + $amount) <= $limit;
}