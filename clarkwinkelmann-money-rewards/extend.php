<?php

namespace ClarkWinkelmann\MoneyRewards;

use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Api\Serializer;
use Flarum\Extend;
use Flarum\Post\Post;
use Flarum\User\User;

use function Tobyz\JsonApiServer\json_api_response;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),

    new Extend\Locales(__DIR__ . '/locale'),

    new Extend\ApiResource(RewardResource::class),

    (new Extend\ApiResource(Resource\ForumResource::class))
        ->fields(ForumAttributes::class),

    (new Extend\ApiResource(Resource\PostResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('rewardWithMoney')
                ->get(fn (Post $post, Context $context) => $context->getActor()->can('rewardWithMoney', $post)),
            Schema\Relationship\ToMany::make('moneyRewards')
                ->type('money-rewards')
                ->includable(),
        ])
        ->endpoints(fn () => [
            Endpoint\Endpoint::make('money-rewards.create')
                ->route('POST', '/{id}/money-rewards')
                ->authenticated()
                ->can('rewardWithMoney')
                // The giver and receiver are included to update their balance in the UI
                ->defaultInclude(['moneyRewards', 'moneyRewards.giver', 'moneyRewards.receiver'])
                ->action(fn (Context $context) => resolve(CreateReward::class)($context)),
        ])
        ->endpoint([Endpoint\Index::class, Endpoint\Show::class], function (Endpoint\Index|Endpoint\Show $endpoint) {
            return $endpoint->addDefaultInclude(['moneyRewards', 'moneyRewards.giver']);
        }),

    (new Extend\ApiResource(Resource\DiscussionResource::class))
        ->endpoint(Endpoint\Show::class, function (Endpoint\Show $endpoint) {
            return $endpoint->addDefaultInclude(['firstPost.moneyRewards', 'firstPost.moneyRewards.giver']);
        }),

    (new Extend\ApiResource(Resource\UserResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('canSeeMoneyRewardHistory')
                ->get(fn (User $user, Context $context) => $context->getActor()->can('seeMoneyRewardHistory', $user)),
        ])
        ->endpoints(fn () => [
            Endpoint\Endpoint::make('money-rewards.history')
                ->route('GET', '/{id}/money-rewards')
                ->can('seeMoneyRewardHistory')
                ->action(function (Context $context) {
                    $user = $context->model;

                    return Reward::query()
                        ->where('giver_user_id', $user->id)
                        ->orWhere('receiver_user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                })
                ->response(function (Context $context, $rewards) {
                    $context = $context->withCollection($context->api->getCollection('money-rewards'));

                    $serializer = new Serializer($context);
                    $resource = $context->resource('money-rewards');

                    foreach ($rewards as $reward) {
                        $serializer->addPrimary($resource, $reward, [
                            'post' => ['discussion' => []],
                            'giver' => [],
                            'receiver' => [],
                        ]);
                    }

                    [$data, $included] = $serializer->serialize();

                    return json_api_response(compact('data', 'included'));
                }),
        ]),

    (new Extend\Policy())
        ->modelPolicy(Post::class, Policies\PostPolicy::class)
        ->modelPolicy(User::class, Policies\UserPolicy::class),

    (new Extend\Model(Post::class))
        ->hasMany('moneyRewards', Reward::class, 'post_id'),
];
