<?php

namespace SrcLab\AutoDeploy\Notifications;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class AutoDeploySuccess extends Notification
{
    /**
     * @var string
     */
    private $pull_request;

    public function __construct($pull_request)
    {
        $this->pull_request = $pull_request;
    }

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
        $message = (new SlackMessage)
            ->success()
            ->content('✅ '.config('app.name').': автодеплой выполнен');

        if(!empty($this->pull_request)) {
            $message->attachment(function ($attachment)  {
                $attachment->title('Пулл')
                    ->content($this->pull_request);
            });
        }

        return $message;
    }
}
