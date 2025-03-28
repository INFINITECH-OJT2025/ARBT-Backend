<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GeneralNotification implements ShouldBroadcast
{
    use SerializesModels;

    public $to;         // 'admin' or 'user'
    public $userId;     // optional, used for user channel
    public $message;    // notification text
    public $type;       // e.g. 'booking', 'shop'

    public function __construct($to, $message, $type = 'general', $userId = null)
    {
        $this->to = $to;
        $this->message = $message;
        $this->type = $type;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        if ($this->to === 'admin') {
            return new PrivateChannel('notifications.admin');
        } else {
            return new PrivateChannel('notifications.user.' . $this->userId);
        }
    }
}
