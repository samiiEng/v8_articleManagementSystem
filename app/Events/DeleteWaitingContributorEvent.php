<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteWaitingContributorEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $articleID;
    public int $to;
    public array $invitationMessagesIDs;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($articleID, $to, $invitationMessagesIDs)
    {
        $this->articleID = $articleID;
        $this->to = $to;
        $this->invitationMessagesIDs = $invitationMessagesIDs;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
