<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreArticleEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $article;
    public array $messages;
    public array $newContributors;
    public bool $ifStopTheNextListener;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($article, $messages, $newContributors = null, $ifStopTheNextListener)
    {
        $this->article = $article;
        $this->messages = $messages;
        $this->newContributors = $newContributors;
        $this->ifStopTheNextListener = $ifStopTheNextListener;
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
