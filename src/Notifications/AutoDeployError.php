<?php

namespace SrcLab\AutoDeploy\Notifications;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class AutoDeployError extends Notification
{
    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $pull_request;

    /**
     * Create a new notification instance.
     *
     * @param string $error
     * @param string $pull_request
     * @return void
     */
    public function __construct($error, $pull_request)
    {
        $this->error = $error;
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
            ->content('🚫 '.config('app.name').': автодеплой не выполнен.')
            ->attachment(function ($attachment)  {
                $attachment->title('Ошибка')
                    ->content($this->error);
            });

        if(!empty($this->pull_request)) {
            $message->attachment(function ($attachment)  {
                $attachment->title('Пулл')
                    ->content($this->pull_request);
            });
        }

        return $message;
    }
}
