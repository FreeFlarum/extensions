<?php

namespace ImDong\SpamAlert\Listener;


use ImDong\SpamAlert\Event\PostSetSpamHide;
use Flarum\Notification\NotificationSyncer;
use ImDong\SpamAlert\Notification\PostSpamHidedBlueprint;

/**
 * 当回复因为被标记 spam 太多被隐藏时
 */
class SendNotificationWhenPostIsSpamHide
{
    public function __construct(protected NotificationSyncer $notifications)
    {
    }

    public function handle(PostSetSpamHide $event)
    {
        if ($event->post->user && $event->post->user->id != $event->user->id) {
            $this->notifications->sync(
                new PostSpamHidedBlueprint($event->post),
                [$event->post->user]
            );
        }
    }
}
