<?php
require_once '../includes/functions.php';
require_once '../includes/oauth.php';

session_start();

$provider = $_GET['provider'] ?? '';

if (handleOAuthCallback($provider)) {
    header('Location: /');
    exit;
} else {
    echo "Authentication failed. <a href='/login'>Try again</a>";
}