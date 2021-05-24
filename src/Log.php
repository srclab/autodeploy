<?php

namespace SrcLab\AutoDeploy;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class Log
{
    /**
     * @var \Monolog\Logger
     */
    private static $log;

    /**
     * Получение экземпляра лога.
     *
     * @return \Monolog\Logger
     */
    public static function log()
    {
        if(!empty(self::$log)) {
            return self::$log;
        }

        self::$log = new Logger('app');
        $handler = new RotatingFileHandler(__DIR__.'/../../../../storage/logs/app.log', 3, Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s', true, true));
        self::$log->pushHandler($handler);

        return self::$log;
    }

}