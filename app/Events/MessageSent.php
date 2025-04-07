<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Chat $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Construct the dynamic channel name based on user_id and admin_id
        $channelName = 'my-channel.' . $this->message->user_id . '+' . $this->message->admin_id;
        
        return new Channel($channelName);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'user_id' => $this->message->user_id,
            'admin_id' => $this->message->admin_id,
            'message' => $this->message->message,
            'is_from_user' => $this->message->is_from_user,
        ];
    }
}
