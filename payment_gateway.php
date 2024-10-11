<?php
// Simulated payment gateway functions

function initiate_payment($amount, $user_id) {
    // In a real implementation, this would create a payment session with the payment gateway
    $payment_id = uniqid('PAY');
    return array(
        'payment_id' => $payment_id,
        'amount' => $amount,
        'status' => 'pending',
        'redirect_url' => "/process-payment?payment_id=$payment_id&amount=$amount&user_id=$user_id"
    );
}

function process_payment($payment_id, $amount, $user_id) {
    // In a real implementation, this would communicate with the payment gateway to process the payment
    $success = (rand(1, 10) > 2); // 80% success rate for simulation
    
    if ($success) {
        // Payment successful
        update_user_balance($user_id, $amount);
        record_transaction($user_id, $amount, 'deposit');
        return array(
            'status' => 'success',
            'message' => "Payment of $amount processed successfully."
        );
    } else {
        // Payment failed
        return array(
            'status' => 'failed',
            'message' => "Payment processing failed. Please try again."
        );
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