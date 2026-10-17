<?php

namespace ClarkWinkelmann\CatchTheFish;

use ClarkWinkelmann\CatchTheFish\Api\ForumResourceFields;
use ClarkWinkelmann\CatchTheFish\Api\Resource\FishResource;
use ClarkWinkelmann\CatchTheFish\Api\Resource\RankingResource;
use ClarkWinkelmann\CatchTheFish\Api\Resource\RoundResource;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;
use Flarum\Extend;
use Flarum\Foundation\Paths;
use Flarum\Http\UrlGenerator;
use Flarum\Post\Post;
use Flarum\User\User;

$fishesRelationship = fn() => [
    Schema\Relationship\ToMany::make('catchTheFishFishes')
        ->type('catchthefish-fishes')
        ->includable(),
];

$includeFishes = fn(Endpoint\Endpoint $endpoint) => $endpoint->addDefaultInclude([
    'catchTheFishFishes.lastUserPlacement',
    'catchTheFishFishes.lastUserNaming',
]);

return [
    (new Extend\Frontend('forum'))
        ->css(__DIR__ . '/resources/less/forum.less')
        ->js(__DIR__ . '/js/dist/forum.js')
        ->route('/catch-the-fish', 'catch-the-fish-ranking')
        ->route('/catch-the-fish/rounds', 'catch-the-fish-rounds')
        ->route('/catch-the-fish/rounds/{id}', 'catch-the-fish-round'),
    (new Extend\Frontend('admin'))
        ->css(__DIR__ . '/resources/less/admin.less')
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Locales(__DIR__ . '/resources/locale')),

    new Extend\ApiResource(RoundResource::class),
    new Extend\ApiResource(FishResource::class),
    new Extend\ApiResource(RankingResource::class),

    (new Extend\Model(Discussion::class))
        ->relationship('catchTheFishFishes', (new ConfigureFishesRelationship('discussion_id_placement'))(...)),
    (new Extend\Model(Post::class))
        ->relationship('catchTheFishFishes', (new ConfigureFishesRelationship('post_id_placement'))(...)),
    (new Extend\Model(User::class))
        ->relationship('catchTheFishFishes', (new ConfigureFishesRelationship('user_id_placement'))(...))
        ->relationship('catchTheFishBasket', ConfigureBasketRelationship::class),

    (new Extend\ApiResource(Resource\ForumResource::class))
        ->fields(ForumResourceFields::class)
        ->endpoint('show', fn(Endpoint\Show $endpoint) => $endpoint->addDefaultInclude([
            'catchTheFishActiveRounds.myRanking',
            'actor.catchTheFishBasket',
        ])),

    (new Extend\Policy())
        ->modelPolicy(Fish::class, Access\FishPolicy::class)
        ->modelPolicy(Round::class, Access\RoundPolicy::class),

    (new Extend\ApiResource(Resource\DiscussionResource::class))
        ->fields($fishesRelationship)
        ->endpoint(['index', 'show', 'update'], $includeFishes),
    (new Extend\ApiResource(Resource\PostResource::class))
        ->fields($fishesRelationship)
        ->endpoint(['index', 'show'], $includeFishes),
    (new Extend\ApiResource(Resource\UserResource::class))
        ->fields($fishesRelationship)
        ->fields(fn() => [
            Schema\Relationship\ToMany::make('catchTheFishBasket')
                ->type('catchthefish-fishes')
                ->includable()
                ->visible(fn(User $user, Context $context) => $context->getActor()->id === $user->id),
        ])
        ->endpoint(['index', 'show', 'update'], $includeFishes)
        ->endpoint('show', fn(Endpoint\Show $endpoint) => $endpoint->addDefaultInclude(['catchTheFishBasket'])),

    (new Extend\Filesystem())
        ->disk('catch-the-fish', function (Paths $paths, UrlGenerator $url) {
            return [
                'root' => "$paths->public/assets/catch-the-fish",
                'url' => $url->to('forum')->path('assets/catch-the-fish'),
            ];
        }),
];
