<?php

namespace SrcLab\AutoDeploy;

use Monolog\Handler\StreamHandler;
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
     * @param string $name
     * @return \Monolog\Logger
     */
    public static function log($name = 'app')
    {
        if(!empty(self::$log)) {
            return self::$log;
        }

        self::$log = new Logger($name);
        self::$log->pushHandler(new StreamHandler(getcwd().'/../storage/logs/', Logger::WARNING));

        return self::$log;
    }

}