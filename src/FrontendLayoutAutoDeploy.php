<?php

namespace SrcLab\AutoDeploy;

class FrontendLayoutAutoDeploy
{
    /**
     * Выполнение автодеплоя для проекта верстки.
     */
    public static function process()
    {
        if(!empty($_SERVER['HTTP_X_HUB_SIGNATURE']) && !empty($_SERVER['HTTP_X_GITHUB_EVENT'])) {

            try {

                /**
                 * Получение конфигурации.
                 */
                $env = Config::getEnvData();

                /**
                 * Выполнение автодеплоя.
                 */
                $result = (new \SrcLab\AutoDeploy\AutoDeploy([
                    'enabled' => $env['APP_AUTO_DEPLOY_ENABLED'] ?? false,
                    'token' => $env['APP_AUTO_DEPLOY_TOKEN'] ?? null,
                    'branch' => $env['APP_AUTO_DEPLOY_BRANCH'] ?? 'developer',
                    'work_dir' => __DIR__.'/../../../../',
                ]))->deploy(file_get_contents('php://input'), $_SERVER['HTTP_X_HUB_SIGNATURE'], $_SERVER['HTTP_X_GITHUB_EVENT'], AutoDeploy::FRONTEND_TYPE);

                /**
                 * Логирование действия.
                 */
                Log::log()->info($result ? 'Успешно выполнен автодеплой.' : 'Вебхук обработан, автодеплой не выполнен.');

                echo 'ok';

            } catch (\Throwable $e) {
                Log::log()->error("Ошибка выполнения автодеплоя: {$e->getMessage()}", [$e]);
                http_response_code(empty($e->getCode()) ? 500 : $e->getCode());
                echo $e->getMessage();
            }

            exit();
        }
    }

}