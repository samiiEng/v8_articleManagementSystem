<?php

namespace App\Listeners;

use App\Events\DeleteWaitingContributorEvent;
use App\Repositories\ArticleRepository;
use App\Repositories\MessageRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class DeleteInvitaionMessageListener
{

    public $messageRepository;
    public $articleRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(MessageRepository $messageRepository, ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(DeleteWaitingContributorEvent $event)
    {
        $i = 0;
        $invitationMessagesIDs = $event->invitationMessagesIDs;
        foreach ($invitationMessagesIDs as $key => $value) {
            $i++;
            if ($key == $event->to) {
                $this->messageRepository->forceDelete($value);

                //Now delete the contributorID/messageID pair from the articles table
                unset($invitationMessagesIDs[$i]);
                //-------------------------- AT THIS POINT WE NEED A CUSTOMIZED ORM -------------------------
                $this->articleRepository->update([["invitation_messages_ref_id" => "= ?"], ["article_id" => "= ?"], [json_encode($invitationMessagesIDs), $event->articleID]]);

                break;
            }
        }

    }
}
