<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    private $data;
    private $channels;
    private $notifiable;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data, $channels = ['database'])
    {
        $this->data = $data;
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;//['database', 'broadcast', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Use the view() method to send the custom email template
        return (new MailMessage)
                    ->subject($this->data['details']['subject'])
                    ->view($this->data['details']['view'], ['details' => $this->data['details']]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'texte' => $this->data['texte'],
            'module' => $this->data["module"],
            'id' => $this->data['id'],
            'auteurId' => $this->data['auteurId']
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable) {
        $this->notifiable = $notifiable;
        return (new BroadcastMessage([
            'notification' => [
                'id' => $this->id,
                'texte' => $this->data['texte'],
                'module' => $this->data['module'],
                'module_id' => $this->data['id']
            ],
            "notifiable_id" => $notifiable->id,
            "unread" => $notifiable->unreadNotifications->count()
        ]))/* ->onConnection('sqs')
            ->onQueue('broadcasts') */;
    }
    
    public function broadcastOn()
    {
        // Broadcasting to a private channel for a specific user
        return new PrivateChannel('notification.' . $this->notifiable->secure_id); // Customize the channel name to target a user
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'notification.posted';
    }

    /**
     * Optional: Get the delay before the notification is sent.
     *
     * @return \DateTime|int|null
     */
    public function delay()
    {
        return now()->addSeconds(30); // Example: delay notification by 30 seconds
    }
}
