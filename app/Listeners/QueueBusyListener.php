<?php

namespace App\Listeners;

use App\Notifications\QueueBusyNotification;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for QueueBusy events.
 *
 * Sends alerts when queue has too many pending jobs.
 * Uses synchronous mail sending to avoid queue dependency.
 */
class QueueBusyListener
{
    /**
     * Handle the QueueBusy event.
     */
    public function handle(QueueBusy $event): void
    {
        // Log the event first (always works)
        Log::critical('Queue is saturated!', [
            'connection' => $event->connection,
            'queue' => $event->queue,
            'size' => $event->size,
        ]);

        // Get alert emails from config
        $alertEmails = config('app.alert_email');

        if (empty($alertEmails)) {
            Log::warning('No alert email configured. Set ALERT_EMAIL in .env');
            return;
        }

        // Parse comma-separated emails
        $emails = array_map('trim', explode(',', $alertEmails));

        try {
            // Send notification synchronously (not queued!)
            Notification::route('mail', $emails)
                ->notifyNow(new QueueBusyNotification($event));

            Log::info('Queue alert notification sent to: ' . implode(', ', $emails));
        } catch (\Exception $e) {
            // If mail fails, just log it - we can't do much else
            Log::error('Failed to send queue alert notification: ' . $e->getMessage());
        }
    }
}
