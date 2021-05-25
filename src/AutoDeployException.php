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
     * @param string $pull_request
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, $pull_request = null)
    {
        parent::__construct($message, $code, $previous);

        $this->sendNotificationAboutError($message, $pull_request);
    }

    /**
     * Отправка оповещения об ошибке.
     *
     * @param string $message
     * @param string $pull_request
     */
    private function sendNotificationAboutError($message, $pull_request = null)
    {
        if(is_laravel()) {
            if (! empty(config('services.github_auto_deploy.notification.slack.enabled'))) {
                if (empty(config('services.github_auto_deploy.notification.slack.hooks_url'))) {
                    Log::error('[Autodeploy|Notification] Не установлен hooks url для уведомлений в Slack');
                } else {
                    (new AutoDeployNotificationModel())->notify(new AutoDeployError($message, $pull_request));
                }
            }
        }
    }
}
