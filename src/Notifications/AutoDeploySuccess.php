<?php

namespace SrcLab\AutoDeploy\Notifications;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class AutoDeploySuccess extends Notification
{
    /**
     * @var array
     */
    private $pull_request = [];

    /**
     * AutoDeploySuccess constructor.
     *
     * @param array $pull_request
     */
    public function __construct(array $pull_request)
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
                $attachment->title($this->pull_request['title'])
                    ->content($this->pull_request['url']);
            });
        }

        return $message;
    }
}
