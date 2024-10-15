<?php
class ErrorHandler {
    private static $isProduction = false;
    private static $errorTypes = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];

    public static function setProductionMode($isProduction) {
        self::$isProduction = $isProduction;
    }

    public static function handleException(Throwable $exception) {
        $logMessage = sprintf(
            "Uncaught exception '%s' with message '%s' in %s on line %d",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        self::logError($logMessage);

        http_response_code(500);
        if (self::$isProduction) {
            echo "An error occurred. Please try again later.";
        } else {
            echo "<pre>Exception: " . $exception->getMessage() . "</pre>";
        }
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorType = isset(self::$errorTypes[$errno]) ? self::$errorTypes[$errno] : 'Unknown Error';
        $logMessage = sprintf(
            "Error: [%s] %s in %s on line %d",
            $errorType,
            $errstr,
            $errfile,
            $errline
        );

        self::logError($logMessage);

        if (!self::$isProduction && ini_get('display_errors')) {
            echo "<pre>$logMessage</pre>";
        } else {
            echo "An error occurred. Please try again later.";
        }

        if ($errno == E_USER_ERROR) {
            exit(1);
        }

        return true;
    }

    public static function logCustomError($message) {
        self::logError("Custom Error: " . $message);
    }

    private static function logError($message) {
        error_log($message);
        // You can add additional logging here, such as logging to a database or external service
    }

    public static function register() {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }
}

// Register the error and exception handlers
ErrorHandler::register();