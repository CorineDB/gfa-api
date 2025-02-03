<?php

namespace App\Notifications;

use App\Traits\Helpers\SmsTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SmsProviderAlertAccountBalanceNotification extends Notification implements ShouldQueue
{
    use Queueable, SmsTrait;

    protected $message;
    protected $subject;
    protected $phoneNumbers;
    protected $emails;

    /**
     * Create a new notification instance.
     *
     * @param string $message
     * @param array $phoneNumbers
     * @param array $emails
     */
    public function __construct($message, $subject='', $phoneNumbers = [], $emails = [])
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->phoneNumbers = $phoneNumbers;
        $this->emails = $emails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = [];

        if (!empty($this->emails)) {
            $channels[] = 'mail';
        }

        if (!empty($this->phoneNumbers)) {
            $channels[] = 'sms';  // Custom SMS channel
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->line($this->message);
    }

    /**
     * Get the SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return void
     */
    public function toSms($notifiable)
    {
        foreach (array_chunk($this->phoneNumbers, 100) as $phoneNumbers) {
            foreach ($phoneNumbers as $phoneNumber) {
                $this->sendSms($this->message, $phoneNumber);
            }
        }
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
            //
        ];
    }
}
