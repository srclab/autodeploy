<?php

namespace SrcLab\AutoDeploy;

use SrcLab\AutoDeploy\Models\Notifications\AutoDeploy as AutoDeployNotificationModel;
use SrcLab\AutoDeploy\Notifications\AutodeployError;
use Exception;
use Throwable;

class AutoDeployException extends Exception
{
    /**
     * AutoDeployException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->sendNotificationAboutError($message);
    }

    /**
     * Отправка оповещения об ошибке.
     */
    private function sendNotificationAboutError($message)
    {
        if(!empty(config('services.github_auto_deploy.notification.slack.enabled'))) {
            if(empty(config('services.github_auto_deploy.notification.slack.hooks_url'))) {
                throw new \Exception('Не установлен hooks url для уведомлений в Slack');
            }

            (new AutoDeployNotificationModel())->notify(new AutodeployError($message));
        }
    }
}
