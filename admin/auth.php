<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

function isAdmin($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn() == 1;
}

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>