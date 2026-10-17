<?php

namespace ClarkWinkelmann\AnonymousPosting\Listener;

use ClarkWinkelmann\AnonymousPosting\Event\PostAnonymized;
use ClarkWinkelmann\AnonymousPosting\Event\PostDeAnonymized;
use Flarum\Post\Event\Saving;
use Illuminate\Support\Arr;

class SavePost extends AbstractAnonymousStateEditor
{
    public function handle(Saving $event)
    {
        $attributes = (array)Arr::get($event->data, 'attributes');

        // Flarum 2 creates the first post of a new discussion with only the content attribute, so we apply the discussion's anonymity
        if (!$event->post->exists && !Arr::exists($attributes, 'isAnonymous') && $event->post->discussion && !$event->post->discussion->first_post_id) {
            $attributes['isAnonymous'] = !!$event->post->discussion->anonymous_user_id;
        }

        $this->apply($event->actor, $event->post, $attributes, PostAnonymized::class, PostDeAnonymized::class);

        // Anonymize deletion/edit author if it's the author of an anonymous post
        if (
            $event->post->isDirty('edited_user_id') &&
            $event->post->anonymous_user_id &&
            $this->anonymityRepository->shouldAnonymizeEdit($event->post, $event->post->edited_user_id)) {
            $event->post->editedUser()->dissociate();
        }
        if (
            $event->post->isDirty('hidden_user_id') &&
            $event->post->anonymous_user_id &&
            $this->anonymityRepository->shouldAnonymizeEdit($event->post, $event->post->hidden_user_id)) {
            $event->post->hiddenUser()->dissociate();
        }
    }
}
