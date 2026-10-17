<?php

namespace V17Development\FlarumUserBadges;

use Askvortsov\AutoModerator\Extend\AutoModerator as AutoModeratorExtender;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\Search\Database\DatabaseSearchDriver;
use Flarum\User\Event\Saving;
use Flarum\User\User;

return [
    // fof/badges uses the same resource type and forum routes, so this extension stays inert while it is enabled
    (new Extend\Conditional())
        ->whenExtensionDisabled('fof-badges', function () {
            $extend = [
                (new Extend\Frontend('forum'))
                    ->js(__DIR__ . '/js/dist/forum.js')
                    ->css(__DIR__ . '/less/forum.less')
                    ->route('/badges', 'badges.overview')
                    ->route('/badges/{id}', 'badges.item'),
                (new Extend\Frontend('admin'))
                    ->js(__DIR__ . '/js/dist/admin.js')
                    ->css(__DIR__ . '/less/admin.less'),

                new Extend\ApiResource(Api\Resource\BadgeResource::class),
                new Extend\ApiResource(Api\Resource\BadgeCategoryResource::class),
                new Extend\ApiResource(Api\Resource\UserBadgeResource::class),

                (new Extend\Routes('api'))
                    ->post('/badges/order', 'badges.order', Api\Controller\OrderBadgesController::class)
                    ->post('/badgeCategories/order', 'badgeCategories.order', Api\Controller\OrderBadgeCategoriesController::class),

                // Extension permissions
                (new Extend\ApiResource(Resource\ForumResource::class))
                    ->fields(fn () => [
                        Schema\Boolean::make('canGiveBadge')
                            ->get(fn ($forum, Context $context) => $context->getActor()->hasPermission('badges.giveBadge')),
                        Schema\Boolean::make('canViewDetailedBadgeUsers')
                            ->get(fn ($forum, Context $context) => $context->getActor()->hasPermission('badges.canViewDetailedUsers')),
                        Schema\Boolean::make('editOwnUserCardBadges')
                            ->get(fn ($forum, Context $context) => $context->getActor()->hasPermission('badges.editOwnUserCardBadges')),
                        Schema\Boolean::make('editUserCardBadges')
                            ->get(fn ($forum, Context $context) => $context->getActor()->hasPermission('badges.editUserCardBadges')),
                    ]),

                // Badges relation with User
                (new Extend\Model(User::class))
                    ->relationship('userBadges', function ($user) {
                        return $user->hasMany(UserBadge\UserBadge::class, 'user_id');
                    })
                    ->relationship('userPrimaryBadge', function ($user) {
                        return $user->hasOne(UserBadge\UserBadge::class, 'user_id', null)->where('is_primary', true);
                    }),

                (new Extend\ApiResource(Resource\UserResource::class))
                    ->fields(fn () => [
                        Schema\Relationship\ToMany::make('userBadges')
                            ->type('userBadges')
                            ->includable(),
                        Schema\Relationship\ToOne::make('userPrimaryBadge')
                            ->type('userBadges')
                            ->includable(),
                        // Saved by the SaveSelectedBadges listener
                        Schema\Arr::make('userCardBadges')
                            ->writable()
                            ->visible(false)
                            ->set(fn () => null),
                    ])
                    ->endpoint(Endpoint\Index::class, function (Endpoint\Index $endpoint) {
                        return $endpoint->addDefaultInclude(['userBadges', 'userBadges.badge']);
                    })
                    ->endpoint([Endpoint\Show::class, Endpoint\Update::class, Endpoint\Create::class], function (Endpoint\Show|Endpoint\Update|Endpoint\Create $endpoint) {
                        return $endpoint->addDefaultInclude(['userBadges', 'userBadges.badge', 'userBadges.badge.category']);
                    }),

                (new Extend\ApiResource(Resource\PostResource::class))
                    ->endpoint([Endpoint\Index::class, Endpoint\Show::class], function (Endpoint\Index|Endpoint\Show $endpoint) {
                        return $endpoint->addDefaultInclude(['user.userBadges', 'user.userBadges.badge']);
                    }),

                (new Extend\ApiResource(Resource\DiscussionResource::class))
                    ->endpoint(Endpoint\Index::class, function (Endpoint\Index $endpoint) {
                        return $endpoint->addDefaultInclude(['user.userBadges', 'user.userBadges.badge']);
                    }),

                (new Extend\SearchDriver(DatabaseSearchDriver::class))
                    ->addSearcher(UserBadge\UserBadge::class, UserBadge\Search\UserBadgeSearcher::class)
                    ->addFilter(UserBadge\Search\UserBadgeSearcher::class, Query\FilterUserBadgesByBadge::class),

                (new Extend\Notification())
                    ->type(Notification\BadgeReceivedBlueprint::class, ['alert']),

                // The notification excerpt shows the received badge
                (new Extend\ApiResource(Resource\NotificationResource::class))
                    ->endpoint([Endpoint\Index::class, Endpoint\Show::class], function (Endpoint\Index|Endpoint\Show $endpoint) {
                        return $endpoint->addDefaultInclude(['subject.badge']);
                    }),

                new Extend\Locales(__DIR__ . '/locale'),

                (new Extend\Event())
                    ->listen(Saving::class, Listeners\SaveSelectedBadges::class),

                (new Extend\Policy())
                    ->modelPolicy(User::class, Access\UserPolicy::class),

                (new Extend\Settings)
                    ->serializeToForum('showBadgesOnUserCard', 'v17development-user-badges.show_badges_on_user_card', 'boolval')
                    ->serializeToForum('numberOfBadgesOnUserCard', 'v17development-user-badges.number_of_badges_on_user_card', 'intval')
                    ->default('v17development-user-badges.number_of_badges_on_user_card', 5),
            ];

            /**
             * Initialize Auto Moderator functionalities when installed
             */
            if (class_exists(AutoModeratorExtender::class)) {
                $extend[] =
                    (new AutoModeratorExtender())
                    ->actionDriver('give_badge', AutoModerator\Action\GiveBadge::class)
                    ->actionDriver('remove_badge', AutoModerator\Action\RemoveBadge::class)
                    ->requirementDriver('has_badge', AutoModerator\Requirement\HasBadge::class)
                    ->metricDriver('badges_received', AutoModerator\Metric\BadgesReceived::class);
            }

            return $extend;
        }),
];
