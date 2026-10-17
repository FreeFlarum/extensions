<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Dislikes;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Dislikes\Api\PostResourceFields;
use Flarum\Dislikes\Event\PostWasDisliked;
use Flarum\Dislikes\Event\PostWasUndisliked;
use Flarum\Dislikes\Notification\PostDislikedBlueprint;
use Flarum\Dislikes\Query\DislikedByFilter;
use Flarum\Dislikes\Query\DislikedFilter;
use Flarum\Extend;
use Flarum\Post\Event\Deleted;
use Flarum\Post\Filter\PostSearcher;
use Flarum\Post\Post;
use Flarum\Search\Database\DatabaseSearchDriver;
use Flarum\User\Search\UserSearcher;
use Flarum\User\User;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Model(Post::class))
        ->belongsToMany('dislikes', User::class, 'post_dislikes', 'post_id', 'user_id'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Notification())
        ->type(PostDislikedBlueprint::class, ['alert']),

    (new Extend\ApiResource(Resource\PostResource::class))
        ->fields(PostResourceFields::class)
        ->endpoint(
            [Endpoint\Index::class, Endpoint\Show::class, Endpoint\Create::class, Endpoint\Update::class],
            function (Endpoint\Index|Endpoint\Show|Endpoint\Create|Endpoint\Update $endpoint): Endpoint\Endpoint {
                return $endpoint->addDefaultInclude(['dislikes']);
            }
        ),

    (new Extend\ApiResource(Resource\DiscussionResource::class))
        ->endpoint(
            [Endpoint\Show::class, Endpoint\Create::class],
            function (Endpoint\Show|Endpoint\Create $endpoint): Endpoint\Endpoint {
                return $endpoint->addDefaultInclude(['firstPost.dislikes', 'lastPost.dislikes']);
            }
        ),

    (new Extend\Event())
        ->listen(PostWasDisliked::class, Listener\SendNotificationWhenPostIsDisliked::class)
        ->listen(PostWasUndisliked::class, Listener\SendNotificationWhenPostIsUndisliked::class)
        ->listen(Deleted::class, function (Deleted $event) {
            $event->post->dislikes()->detach();
        }),

    (new Extend\Settings())
        ->default('nodeloc-dislikes.dislike_own_post', true),

    (new Extend\Policy())
        ->modelPolicy(Post::class, Access\DislikePostPolicy::class),

    (new Extend\SearchDriver(DatabaseSearchDriver::class))
        ->addFilter(PostSearcher::class, DislikedByFilter::class)
        ->addFilter(UserSearcher::class, DislikedFilter::class),
];
