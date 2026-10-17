<?php

namespace ClarkWinkelmann\ShadowBan\Listener;

use Carbon\Carbon;
use ClarkWinkelmann\ShadowBan\Event\ShadowHiddenPost;
use Flarum\Post\CommentPost;
use Flarum\Post\Event\Saving;
use Flarum\Post\Post;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;

class SavePost
{
    public function __construct(protected Dispatcher $events)
    {
    }

    public function handle(Saving $event)
    {
        if (!$event->post->exists && $event->post->user_id && $event->post instanceof CommentPost) {
            $user = User::find($event->post->user_id);

            if ($user && $user->shadow_banned_until && $user->shadow_banned_until->isFuture()) {
                // Don't shadow-hide the first post of a discussion since the discussion is already hidden in that situation
                if (Post::query()->where('discussion_id', $event->post->discussion_id)->exists()) {
                    $event->post->shadow_hidden_at = Carbon::now();

                    // Manually dispatch the event here so we can keep $actor null to signal it was done automatically
                    $event->post->afterSave(function ($post) {
                        $this->events->dispatch(new ShadowHiddenPost($post));
                    });
                }
            }
        }
    }
}
