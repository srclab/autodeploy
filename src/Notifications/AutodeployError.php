<?php

namespace SrcLab\AutoDeploy\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class AutodeployError extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    private $error;

    /**
     * Create a new notification instance.
     *
     * @param string $error
     * @return void
     */
    public function __construct($error)
    {
        $this->error = $error;
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
        return (new SlackMessage)
            ->success()
            ->content('🚫 '.config('app.name').': автодеплой не выполнен.')
            ->attachment(function ($attachment)  {
                $attachment->title('Ошибка')
                    ->content($this->error);
            });
    }
}
