<?php

class Logger {
    private static $logFile = '/var/log/app/application.log';

    public static function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        
        if (!file_exists(dirname(self::$logFile))) {
            mkdir(dirname(self::$logFile), 0777, true);
        }
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }

    public static function info($message) {
        self::log($message, 'INFO');
    }

    public static function warning($message) {
        self::log($message, 'WARNING');
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }
}