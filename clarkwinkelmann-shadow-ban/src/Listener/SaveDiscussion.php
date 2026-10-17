<?php

namespace ClarkWinkelmann\ShadowBan\Listener;

use Carbon\Carbon;
use ClarkWinkelmann\ShadowBan\Event\ShadowHiddenDiscussion;
use Flarum\Discussion\Event\Saving;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;

class SaveDiscussion
{
    public function __construct(protected Dispatcher $events)
    {
    }

    public function handle(Saving $event)
    {
        if (!$event->discussion->exists && $event->discussion->user_id) {
            $user = User::find($event->discussion->user_id);

            if ($user && $user->shadow_banned_until && $user->shadow_banned_until->isFuture()) {
                $event->discussion->shadow_hidden_at = Carbon::now();

                // Manually dispatch the event here so we can keep $actor null to signal it was done automatically
                $event->discussion->afterSave(function ($discussion) {
                    $this->events->dispatch(new ShadowHiddenDiscussion($discussion));
                });
            }
        }
    }
}
