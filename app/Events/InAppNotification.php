<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InAppNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    public $receiverIds;

    /**
     * Create a new event instance.
     *
     * @param array $notification The notification data
     * @param array $receiverIds The list of receiver IDs (can be organization IDs or user IDs)
     * @return void
     */
    public function __construct($notification, $receiverIds)
    {
        $this->notification = $notification;
        $this->receiverIds  = $receiverIds; // Organization IDs or user IDs
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Use a wildcard to broadcast the event to multiple organizations
        return new Channel('notifications.' . implode(',', $this->receiverIds));
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
}
