<?php

namespace SrcLab\AutoDeploy;

use SrcLab\AutoDeploy\Models\AutoDeploy as AutoDeployNotificationModel;
use SrcLab\AutoDeploy\Notifications\AutoDeployError;
use Illuminate\Support\Facades\Log;
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
     *
     * @param string $message
     */
    private function sendNotificationAboutError($message)
    {
        if(!empty(config('services.github_auto_deploy.notification.slack.enabled'))) {
            if(empty(config('services.github_auto_deploy.notification.slack.hooks_url'))) {
                Log::error('[Autodeploy|Notification] Не установлен hooks url для уведомлений в Slack');
            } else {
                (new AutoDeployNotificationModel())->notify(new AutoDeployError($message));
            }
        }
    }
}
