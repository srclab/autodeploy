<?php

namespace SrcLab\AutoDeploy\Notifications;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class AutoDeploySuccess extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->content('✅ '.config('app.name').': автодеплой выполнен');
    }
}