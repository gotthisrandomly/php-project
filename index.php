<?php
// Main entry point for the application
session_start();
require_once 'includes/autoloader.php';
require_once 'includes/functions.php';
require_once 'includes/ErrorHandler.php';
require_once 'includes/Logger.php';

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
        '/password-reset' => ['PasswordResetController', 'index'],
        '/password-reset/reset' => ['PasswordResetController', 'reset'],
        '/api' => ['ApiController', 'handleRequest'],
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
        $appControllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        } elseif (file_exists($appControllerFile)) {
            require_once $appControllerFile;
        } else {
            ErrorHandler::logCustomError("Controller file for $controllerName not found");
            throw new Exception("Controller file for $controllerName not found");
        }
        
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