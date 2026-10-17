<?php

namespace ClarkWinkelmann\AnonymousPosting;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving as DiscussionSaving;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Discussion\Search\Filter\AuthorFilter as DiscussionAuthorFilter;
use Flarum\Extend;
use Flarum\Post\Event\Posted;
use Flarum\Post\Event\Restored;
use Flarum\Post\Event\Revised;
use Flarum\Post\Event\Saving as PostSaving;
use Flarum\Post\Filter\AuthorFilter as PostAuthorFilter;
use Flarum\Post\Filter\PostSearcher;
use Flarum\Post\Post;
use Flarum\Search\Database\DatabaseSearchDriver;
use Flarum\Settings\Event\Saving as EventSaving;
use Flarum\User\User;
use Kilowhat\Formulaire\Submission;

// Remove the placeholder user model in case it ends up here, we don't want it in the API response
$hidePlaceholderUser = function (Schema\Relationship\ToOne $field) {
    return $field->get(function (AbstractModel $model) {
        return $model->user instanceof AnonymousUser ? null : $model->user;
    });
};

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\Event())
        ->listen(Posted::class, Listener\NotifyMentionWhenVisible::class)
        ->listen(Restored::class, Listener\NotifyMentionWhenVisible::class)
        ->listen(Revised::class, Listener\NotifyMentionWhenVisible::class)
        ->listen(DiscussionSaving::class, Listener\SaveDiscussion::class)
        ->listen(PostSaving::class, Listener\SavePost::class)
        ->listen(EventSaving::class, Listener\SaveSettings::class),

    ...array_map(function (string $className) {
        return (new Extend\Model($className))
            ->belongsTo('anonymousUser', User::class, 'anonymous_user_id')
            // This relationship doesn't exist in Formulaire extension because it doesn't make much sense to retrieve public data this way
            // (because it needs to check which forms are visible to the current user first)
            // But for our use case here we don't care about form visibility or open/close status, so we can just get all of them
            // Setting it on the post/discussion also saves up one database request by skipping the anonymousUser relationship
            ->relationship('anonymousUserSubmissions', function (AbstractModel $model) {
                return $model->hasMany(Submission::class, 'link_id', 'anonymous_user_id')
                    ->where('link_type', 'users')
                    ->whereNull('hidden_at');
            });
    }, [Discussion::class, Post::class]),

    (new Extend\Settings())
        ->default('anonymous-posting.composerHelpTextPosition', 'visible'),

    // The anonymousUser relationship is only visible to actors with the reveal permission
    (new Extend\ApiResource(Resource\DiscussionResource::class))
        ->fields(DiscussionAndPostAttributes::class)
        ->field('user', $hidePlaceholderUser)
        ->endpoint([Endpoint\Index::class, Endpoint\Create::class], function (Endpoint\Index|Endpoint\Create $endpoint) {
            return $endpoint->addDefaultInclude(['anonymousUser']);
        })
        ->endpoint(Endpoint\Show::class, function (Endpoint\Show $endpoint) {
            return $endpoint->addDefaultInclude(['anonymousUser', 'firstPost.anonymousUser']);
        })
        ->endpoint(Endpoint\Update::class, function (Endpoint\Update $endpoint) {
            return $endpoint->addDefaultInclude(['user', 'anonymousUser']); // user needed for correct refresh when de-anonymizing
        }),

    (new Extend\ApiResource(Resource\PostResource::class))
        ->fields(DiscussionAndPostAttributes::class)
        ->field('user', $hidePlaceholderUser)
        ->endpoint([Endpoint\Index::class, Endpoint\Show::class, Endpoint\Create::class], function (Endpoint\Index|Endpoint\Show|Endpoint\Create $endpoint) {
            return $endpoint->addDefaultInclude(['anonymousUser']);
        })
        ->endpoint(Endpoint\Update::class, function (Endpoint\Update $endpoint) {
            return $endpoint->addDefaultInclude(['user', 'anonymousUser']); // user needed for correct refresh when de-anonymizing
        }),

    (new Extend\ApiResource(Resource\ForumResource::class))
        ->fields(ForumAttributes::class),

    (new Extend\Policy())
        ->modelPolicy(Discussion::class, Policy\DiscussionPolicy::class)
        ->modelPolicy(Post::class, Policy\PostPolicy::class),

    (new Extend\SearchDriver(DatabaseSearchDriver::class))
        ->replaceFilter(DiscussionSearcher::class, DiscussionAuthorFilter::class, Filter\DiscussionAuthorFilter::class)
        ->replaceFilter(PostSearcher::class, PostAuthorFilter::class, Filter\PostAuthorFilter::class),

    (new Extend\ServiceProvider())
        ->register(FakeUserRelation\FakeUserRelationServiceProvider::class),
];
