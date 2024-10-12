<?php
// Simulated OAuth functions

function get_google_auth_url() {
    // In a real implementation, this would generate a proper OAuth URL for Google
    return '/oauth/google';
}

function get_facebook_auth_url() {
    // In a real implementation, this would generate a proper OAuth URL for Facebook
    return '/oauth/facebook';
}

function handle_google_callback($code) {
    // In a real implementation, this would exchange the code for an access token
    // and fetch the user's information from Google
    $user_info = array(
        'email' => 'user@example.com',
        'name' => 'John Doe',
        'google_id' => '123456789'
    );
    return create_or_login_oauth_user($user_info, 'google');
}

function handle_facebook_callback($code) {
    // In a real implementation, this would exchange the code for an access token
    // and fetch the user's information from Facebook
    $user_info = array(
        'email' => 'user@example.com',
        'name' => 'Jane Doe',
        'facebook_id' => '987654321'
    );
    return create_or_login_oauth_user($user_info, 'facebook');
}

function create_or_login_oauth_user($user_info, $provider) {
    global $db;
    
    // Check if the user already exists
    $query = "SELECT * FROM users WHERE email = $1";
    $result = pg_query_params($db, $query, array($user_info['email']));
    $user = pg_fetch_assoc($result);
    
    if ($user) {
        // User exists, update OAuth information
        $update_query = "UPDATE users SET {$provider}_id = $1 WHERE id = $2";
        pg_query_params($db, $update_query, array($user_info["{$provider}_id"], $user['id']));
    } else {
        // Create new user
        $insert_query = "INSERT INTO users (username, email, {$provider}_id) VALUES ($1, $2, $3) RETURNING id";
        $result = pg_query_params($db, $insert_query, array($user_info['name'], $user_info['email'], $user_info["{$provider}_id"]));
        $user = pg_fetch_assoc($result);
    }
    
    // Log the user in
    $_SESSION['user_id'] = $user['id'];
    return true;
}