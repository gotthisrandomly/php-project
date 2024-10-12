<?php

function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $errorType = [
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

    $errorMessage = isset($errorType[$errno]) ? $errorType[$errno] : 'Unknown Error';
    $errorMessage .= ": $errstr in $errfile on line $errline";

    error_log($errorMessage);

    if (ini_get('display_errors')) {
        echo "<pre>$errorMessage</pre>";
    } else {
        echo "An error occurred. Please try again later.";
    }

    if ($errno == E_USER_ERROR) {
        exit(1);
    }
}

set_error_handler('customErrorHandler');

function customExceptionHandler($exception) {
    $errorMessage = "Uncaught Exception: " . $exception->getMessage() . 
                    " in " . $exception->getFile() . 
                    " on line " . $exception->getLine();

    error_log($errorMessage);

    if (ini_get('display_errors')) {
        echo "<pre>$errorMessage</pre>";
    } else {
        echo "An error occurred. Please try again later.";
    }
}

set_exception_handler('customExceptionHandler');