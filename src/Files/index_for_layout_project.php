<?php /** @noinspection ALL */

namespace App;

require '../vendor/autoload.php';

/**
 * Автодеплой.
 */
if(!empty($_SERVER['HTTP_X_HUB_SIGNATURE']) && !empty($_SERVER['HTTP_X_GITHUB_EVENT'])) {

    /**
     * Логирование ошибки.
     *
     * @param string $message
     * @param array $data
     * @param string $level
     */
    function log($message, array $data = [], $level = 'ERROR') {

        $path = '../storage/logs/';

        if(!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        file_put_contents($path.'app-'.date('Y-m-d').'.log', '['.date('Y-m-d H:m:i')."] {$level}: $message\n".json_encode($data)."\n", FILE_APPEND | LOCK_EX);

    }

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
        ]))->deploy(file_get_contents('php://input'), $_SERVER['HTTP_X_HUB_SIGNATURE'], $_SERVER['HTTP_X_GITHUB_EVENT']);

        /**
         * Логирование действия.
         */
        log($result ? 'Успешно выполнен автодеплой.' : 'Вебхук обработан, автодеплой не выполнен.', [], 'INFO');

        echo 'ok';

    } catch (\Throwable $e) {
        log("Ошибка выполнения автодеплоя: {$e->getMessage()}", [$e]);
        http_response_code(empty($e->getCode()) ? 500 : $e->getCode());
        echo $e->getMessage();
    }

    exit();
}

/**
 * В случае если автодеплой не потребовался, вывод дефолтного идексного файла.
 */
include "index.html";