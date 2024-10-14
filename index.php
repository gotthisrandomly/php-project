<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/includes/autoloader.php';
require_once __DIR__ . '/includes/ErrorHandler.php';

// Determine if we're in production mode (you can set this based on your deployment process)
$isProduction = false; // Set to true for production environment

ErrorHandler::setProductionMode($isProduction);
set_exception_handler([ErrorHandler::class, 'handleException']);
set_error_handler([ErrorHandler::class, 'handleError']);

// Front Controller
class FrontController {
    private $routes = [
        '/' => ['HomeController', 'index'],
        '/login' => ['LoginController', 'index'],
        '/signup' => ['SignupController', 'index'],
        '/blackjack' => ['BlackjackController', 'index'],
        '/roulette' => ['RouletteController', 'index'],
        '/slot-machine' => ['SlotMachineController', 'index'],
        '/admin' => ['AdminController', 'index'],
        '/deposit' => ['DepositController', 'index'],
        '/cashapp' => ['CashappController', 'index'],
        '/payment' => ['PaymentGatewayController', 'index'],
        '/responsible-gambling' => ['ResponsibleGamblingController', 'index'],
    ];

    public function run() {
        $uri = $this->getUri();
        
        if (array_key_exists($uri, $this->routes)) {
            list($controllerName, $methodName) = $this->routes[$uri];
            $this->loadController($controllerName, $methodName);
        } else {
            $this->loadView('404');
        }
    }

    private function getUri() {
        $uri = $_SERVER['REQUEST_URI'];
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        return $uri;
    }

    private function loadController($controllerName, $methodName) {
        $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $methodName)) {
                    $controller->$methodName();
                } else {
                    ErrorHandler::logCustomError("Method $methodName not found in $controllerName");
                    throw new Exception("Method $methodName not found in $controllerName");
                }
            } else {
                ErrorHandler::logCustomError("Controller class $controllerName not found");
                throw new Exception("Controller class $controllerName not found");
            }
        } else {
            ErrorHandler::logCustomError("Controller file $controllerFile not found");
            throw new Exception("Controller file $controllerFile not found");
        }
    }

    private function loadView($viewName) {
        $viewFile = __DIR__ . '/views/' . $viewName . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            ErrorHandler::logCustomError("View file $viewFile not found");
            throw new Exception("View file $viewFile not found");
        }
    }
}

// Run the application
try {
    $frontController = new FrontController();
    $frontController->run();
} catch (Exception $e) {
    ErrorHandler::handleException($e);
}