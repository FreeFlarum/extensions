<?php

namespace ClarkWinkelmann\ShadowBan;

use Carbon\Carbon;
use ClarkWinkelmann\ShadowBan\Event\ShadowHiddenPost;
use ClarkWinkelmann\ShadowBan\Event\ShadowRestoredPost;
use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Post\Post;

class PostAttributes
{
    public function __invoke(): array
    {
        $canShadowHide = fn (Post $post, Context $context) => $context->getActor()->can('shadowHide', $post);

        return [
            Schema\Boolean::make('isShadowHidden')
                ->visible($canShadowHide)
                ->writable($canShadowHide)
                ->get(fn (Post $post) => !is_null($post->shadow_hidden_at))
                ->set(function (Post $post, bool $isShadowHidden, Context $context) {
                    $post->shadow_hidden_at = $isShadowHidden ? Carbon::now() : null;

                    if ($isShadowHidden) {
                        $post->raise(new ShadowHiddenPost($post, $context->getActor()));
                    } else {
                        $post->raise(new ShadowRestoredPost($post, $context->getActor()));
                    }

                    // Refresh discussion meta, just like Flarum would if the post was deleted/restored
                    // We don't need to do it after a new post because that's handled by Flarum, however we need it here
                    $post->afterSave(function (Post $post) {
                        $discussion = $post->discussion;

                        $discussion->refreshCommentCount();
                        $discussion->refreshLastPost();
                        $discussion->refreshParticipantCount();
                        $discussion->save();
                    });
                }),
            Schema\Boolean::make('canShadowHide')
                ->visible($canShadowHide)
                ->get(fn () => true),
        ];
    }
}
