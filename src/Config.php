<?php

namespace SrcLab\AutoDeploy;

class Config
{
    /**
     * Получение конфигурации из .env.
     *
     * @return array
     */
    public static function getEnvData()
    {
        try {
            return \Dotenv\Dotenv::createImmutable(__DIR__.'/../../../../')->load();
        } catch (\Dotenv\Exception\InvalidPathException $e) {
            return [];
        }
    }
}