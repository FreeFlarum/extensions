<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Dislikes\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Dislikes\Event\PostWasDisliked;
use Flarum\Dislikes\Event\PostWasUndisliked;
use Flarum\Post\Post;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Expression;

class PostResourceFields
{
    public static int $maxDislikes = 4;

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('isDisliked')
                ->visible(false)
                ->writable(fn (Post $post, Context $context) => $context->getActor()->can('dislike', $post))
                ->set(function (Post $post, bool $disliked, Context $context) {
                    $actor = $context->getActor();

                    $currentlyDisliked = $post->dislikes()->where('user_id', $actor->id)->exists();

                    if ($disliked && ! $currentlyDisliked) {
                        $post->dislikes()->attach($actor->id);

                        $post->raise(new PostWasDisliked($post, $actor));
                    } elseif ($currentlyDisliked) {
                        $post->dislikes()->detach($actor->id);

                        $post->raise(new PostWasUndisliked($post, $actor));
                    }
                }),

            Schema\Boolean::make('canDislike')
                ->get(fn (Post $post, Context $context) => $context->getActor()->can('dislike', $post)),
            Schema\Integer::make('dislikesCount')
                ->countRelation('dislikes'),

            Schema\Relationship\ToMany::make('dislikes')
                ->type('users')
                ->includable()
                ->scope(function (BelongsToMany $query, Context $context) {
                    $actor = $context->getActor();
                    $grammar = $query->getQuery()->getGrammar();

                    // So that we can tell if the current user has disliked the post.
                    $query
                        ->orderBy(new Expression($grammar->wrap('user_id').' = '.$actor->id), 'desc')
                        ->limit(static::$maxDislikes);
                }),
        ];
    }
}
