<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Router
$request = $_SERVER['REQUEST_URI'];
$viewDir = '/home/engine/app/project/php_project/public/views';

switch ($request) {
    case '/':
        require $viewDir . '/home.php';
        break;
    case '/login':
        require $viewDir . '/login.php';
        break;
    case '/signup':
        require $viewDir . '/signup.php';
        break;
    case '/admin':
        require $viewDir . '/admin.php';
        break;
    case '/slot-machine':
        require $viewDir . '/slot-machine.php';
        break;
    case '/roulette':
        require $viewDir . '/roulette.php';
        break;
    case '/deposit':
        require $viewDir . '/deposit.php';
        break;
    case '/logout':
        session_destroy();
        header('Location: /');
        exit;
    default:
        http_response_code(404);
        require $viewDir . '/404.php';
        break;
}