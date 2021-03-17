<?php

namespace SrcLab\AutoDeploy\Models\Notifications;

use App\Models\Model;
use Illuminate\Notifications\Notifiable;

class AutoDeploy extends Model
{
    use Notifiable;

    /**
     * Route notifications for the Slack channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return config('services.github_auto_deploy.notification.slack.hooks_url');
    }
}