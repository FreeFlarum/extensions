<?php

namespace ClarkWinkelmann\MoneyToAll;

use Flarum\Api\Resource;

/**
 * Only exists to serialize the subject of notifications.
 *
 * @extends Resource\AbstractDatabaseResource<Record>
 */
class RecordResource extends Resource\AbstractDatabaseResource
{
    public function type(): string
    {
        return 'moneyToAll';
    }

    public function model(): string
    {
        return Record::class;
    }

    public function endpoints(): array
    {
        return [];
    }

    public function fields(): array
    {
        return [];
    }
}
