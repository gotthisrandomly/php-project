<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/autoloader.php';
require_once __DIR__ . '/includes/error_handler.php';

// Define routes
$routes = [
    '/' => 'HomeController',
    '/login' => 'login',
    '/signup' => 'signup',
    '/blackjack' => 'blackjack',
    '/roulette' => 'roulette',
    '/slot-machine' => 'slot-machine',
    '/admin' => 'admin',
    '/deposit' => 'deposit',
    '/cashapp' => 'cashapp',
    '/payment' => 'payment_gateway',
    '/responsible-gambling' => 'responsible_gambling',
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
        
        // Convert controller name to class name (e.g., 'login' to 'LoginController')
        $className = ucfirst($controllerName) . 'Controller';
        
        // Check if the class exists, if not, use the original name
        if (class_exists($className)) {
            $controller = new $className();
        } else {
            $controller = new $controllerName();
        }
        
        // Call the index method if it exists, otherwise call the controller directly
        if (method_exists($controller, 'index')) {
            $controller->index();
        } else {
            $controller();
        }
    } else {
        // Handle 404
        require_once __DIR__ . '/views/404.php';
    }
} else {
    // Handle 404
    require_once __DIR__ . '/views/404.php';
}