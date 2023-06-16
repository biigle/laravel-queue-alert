<?php

namespace Biigle\QueueAlert\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Alert extends Notification
{
    /**
     * Queue connection name.
     *
     * @var string
     */
    public $connections;

    /**
     * Queue name.
     *
     * @var string
     */
    public $queue;

    /**
     * Queue size.
     *
     * @var string
     */
    public $size;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $connection, string $queue, int $size)
    {
        $this->connection = $connection;
        $this->queue = $queue;
        $this->size = $size;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject("Alert for queue {$this->connection}:{$this->queue}")
            ->line("The queue {$this->connection}:{$this->queue} is currently busy with {$this->size} jobs!");
    }
}
