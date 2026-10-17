<?php

namespace ClarkWinkelmann\MoneyRewards;

use Flarum\Api\Resource;
use Flarum\Api\Schema;

/**
 * @extends Resource\AbstractDatabaseResource<Reward>
 */
class RewardResource extends Resource\AbstractDatabaseResource
{
    public function type(): string
    {
        return 'money-rewards';
    }

    public function model(): string
    {
        return Reward::class;
    }

    public function endpoints(): array
    {
        return [];
    }

    public function fields(): array
    {
        return [
            Schema\Number::make('amount'),
            Schema\Boolean::make('new_money'),
            Schema\Str::make('comment'),
            Schema\DateTime::make('createdAt'),

            Schema\Relationship\ToOne::make('post')
                ->type('posts')
                ->includable(),
            Schema\Relationship\ToOne::make('giver')
                ->type('users')
                ->includable(),
            Schema\Relationship\ToOne::make('receiver')
                ->type('users')
                ->includable(),
        ];
    }
}
