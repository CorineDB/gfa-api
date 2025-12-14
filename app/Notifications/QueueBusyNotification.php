<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Events\QueueBusy;

/**
 * Notification sent when queue has too many pending jobs.
 *
 * IMPORTANT: This notification is sent SYNCHRONOUSLY (not queued)
 * to avoid circular dependency when queue is saturated.
 */
class QueueBusyNotification extends Notification
{
    // Note: NOT implementing ShouldQueue - must be synchronous!

    public string $connection;
    public string $queue;
    public int $size;

    public function __construct(QueueBusy $event)
    {
        $this->connection = $event->connection;
        $this->queue = $event->queue;
        $this->size = $event->size;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ [ALERT] Queue Saturée - ' . config('app.name'))
            ->greeting('Alerte Queue!')
            ->line("La file d'attente a dépassé le seuil configuré.")
            ->line('**Connexion:** ' . $this->connection)
            ->line('**Queue:** ' . $this->queue)
            ->line('**Jobs en attente:** ' . $this->size)
            ->line('Veuillez vérifier le worker et la capacité de traitement.')
            ->action('Voir le serveur', config('app.url'))
            ->salutation('Système de monitoring automatique');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'connection' => $this->connection,
            'queue' => $this->queue,
            'size' => $this->size,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
