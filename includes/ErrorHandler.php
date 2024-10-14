<?php
class ErrorHandler {
    private static $isProduction = false;

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
            echo "Exception: " . $exception->getMessage();
        }
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $logMessage = sprintf(
            "Error: [%s] %s in %s on line %d",
            $errno,
            $errstr,
            $errfile,
            $errline
        );

        self::logError($logMessage);

        if (!self::$isProduction) {
            echo "Error: " . $errstr;
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
}