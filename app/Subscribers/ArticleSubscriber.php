<?php

namespace App\Subscribers;

use App\Events\DeleteWaitingContributorEvent;
use App\Events\StoreArticleEvent;
use App\Listeners\DeleteInvitaionMessageListener;
use App\Listeners\SendContributorInvitationMessage;

class ArticleSubscriber
{
    public function __construct()
    {
    }

    public function subscribe($events)
    {
        $events->listen(
            StoreArticleEvent::class,
            [SendContributorInvitationMessage::class, 'handle']
        );

        $events->listen(
            DeleteWaitingContributorEvent::class,
            [DeleteInvitaionMessageListener::class, 'handle']
        );

    }
}
