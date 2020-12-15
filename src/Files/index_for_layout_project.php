<?php /** @noinspection ALL */

namespace App;

use SrcLab\AutoDeploy\Log;

require '../vendor/autoload.php';

/**
 * Автодеплой.
 */
if(!empty($_SERVER['HTTP_X_HUB_SIGNATURE']) && !empty($_SERVER['HTTP_X_GITHUB_EVENT'])) {

    /**
     * Загрузка конфига из .env.
     */
    try {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__.'/../');
        $env = $dotenv->load();
    } catch (\Dotenv\Exception\InvalidPathException $e) {
        $env = [];
    }

    try {

        /**
         * Выполнение автодеплоя.
         */
        $result = (new \SrcLab\AutoDeploy\AutoDeploy([
            'enabled' => $env['APP_AUTO_DEPLOY_ENABLED'] ?? false,
            'token' => $env['APP_AUTO_DEPLOY_TOKEN'] ?? null,
            'branch' => $env['APP_AUTO_DEPLOY_BRANCH'] ?? 'developer',
            'work_dir' => __DIR__.'/../',
        ]))->deploy(file_get_contents('php://input'), $_SERVER['HTTP_X_HUB_SIGNATURE'], $_SERVER['HTTP_X_GITHUB_EVENT'], \SrcLab\AutoDeploy\AutoDeploy::FRONTEND_TYPE);

        /**
         * Логирование действия.
         */
        Log::log('info_autodeploy')->info($result ? 'Успешно выполнен автодеплой.' : 'Вебхук обработан, автодеплой не выполнен.');

        echo 'ok';

    } catch (\Throwable $e) {
        Log::log('info_autodeploy')->error("Ошибка выполнения автодеплоя: {$e->getMessage()}", [$e]);
        http_response_code(empty($e->getCode()) ? 500 : $e->getCode());
        echo $e->getMessage();
    }

    exit();
}

/**
 * В случае если автодеплой не потребовался, вывод дефолтного идексного файла.
 */
include "index.html";