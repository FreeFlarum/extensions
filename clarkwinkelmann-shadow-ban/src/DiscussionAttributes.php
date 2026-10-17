<?php

namespace ClarkWinkelmann\ShadowBan;

use Carbon\Carbon;
use ClarkWinkelmann\ShadowBan\Event\ShadowHiddenDiscussion;
use ClarkWinkelmann\ShadowBan\Event\ShadowRestoredDiscussion;
use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;

class DiscussionAttributes
{
    public function __invoke(): array
    {
        $canShadowHide = fn (Discussion $discussion, Context $context) => $context->getActor()->can('shadowHide', $discussion);

        return [
            Schema\Boolean::make('isShadowHidden')
                ->visible($canShadowHide)
                ->writable($canShadowHide)
                ->get(fn (Discussion $discussion) => !is_null($discussion->shadow_hidden_at))
                ->set(function (Discussion $discussion, bool $isShadowHidden, Context $context) {
                    $discussion->shadow_hidden_at = $isShadowHidden ? Carbon::now() : null;

                    if ($isShadowHidden) {
                        $discussion->raise(new ShadowHiddenDiscussion($discussion, $context->getActor()));
                    } else {
                        $discussion->raise(new ShadowRestoredDiscussion($discussion, $context->getActor()));
                    }
                }),
            Schema\Boolean::make('canShadowHide')
                ->visible($canShadowHide)
                ->get(fn () => true),
        ];
    }
}
