<?php
class ErrorHandler {
    public static function handleException(Throwable $exception) {
        $logMessage = sprintf(
            "Uncaught exception '%s' with message '%s' in %s on line %d",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        error_log($logMessage);

        http_response_code(500);
        echo "An error occurred. Please try again later.";
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

        error_log($logMessage);

        return true;
    }
}