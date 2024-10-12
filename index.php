<?php

require_once __DIR__ . '/includes/autoloader.php';
require_once __DIR__ . '/includes/error_handler.php';

// Define routes
$routes = [
    '/' => 'HomeController',
    '/login' => 'LoginController',
    '/signup' => 'SignupController',
    '/blackjack' => 'BlackjackController',
    '/roulette' => 'RouletteController',
    '/slot-machine' => 'SlotMachineController',
    '/admin' => 'AdminController',
    '/deposit' => 'DepositController',
    '/cashapp' => 'CashappController',
    '/payment' => 'PaymentGatewayController',
    '/responsible-gambling' => 'ResponsibleGamblingController',
];

// Get the current URI
$uri = $_SERVER['REQUEST_URI'];

// Remove query string
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

// Route to the appropriate controller
if (isset($routes[$uri])) {
    $controllerName = $routes[$uri];
    $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $controllerName();
        $controller->index();
    } else {
        // Handle 404
        require_once __DIR__ . '/views/404.php';
    }
} else {
    // Handle 404
    require_once __DIR__ . '/views/404.php';
}