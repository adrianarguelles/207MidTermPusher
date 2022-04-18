<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Message implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $username;
    public $room_id;
    public $message;
    public $attachment_path;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($username, $room_id, $message, $attachment)
    {
        $this->username = $username;
        $this->room_id = $room_id;
        $this->message = $message;
        $this->attachment_path = $attachment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('chat');
    }
    
    public function broadcastAs()
    {
        return 'message';
    }    
}
