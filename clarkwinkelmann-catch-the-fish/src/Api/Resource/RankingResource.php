<?php

namespace ClarkWinkelmann\CatchTheFish\Api\Resource;

use ClarkWinkelmann\CatchTheFish\Ranking;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;

/**
 * @extends AbstractDatabaseResource<Ranking>
 */
class RankingResource extends AbstractDatabaseResource
{
    public function type(): string
    {
        return 'catchthefish-rankings';
    }

    public function model(): string
    {
        return Ranking::class;
    }

    public function endpoints(): array
    {
        return [];
    }

    public function fields(): array
    {
        return [
            Schema\Integer::make('catch_count'),

            Schema\Relationship\ToOne::make('user')
                ->type('users')
                ->includable(),
        ];
    }
}
