<?php

namespace ClarkWinkelmann\DiscussionBookmarks\Api;

use Carbon\Carbon;
use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;

class DiscussionResourceFields
{
    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('bookmarked')
                ->writable(fn (Discussion $discussion, Context $context) => $context->updating())
                ->get(fn (Discussion $discussion) => $discussion->state ? !is_null($discussion->state->bookmarked_at) : false)
                ->set(function (Discussion $discussion, bool $bookmarked, Context $context) {
                    $actor = $context->getActor();

                    $actor->assertRegistered();

                    $state = $discussion->stateFor($actor);

                    $state->bookmarked_at = $bookmarked ? Carbon::now() : null;

                    $discussion->setRelation('state', $state);
                    $discussion->afterSave(fn () => $state->save());
                }),
        ];
    }
}
