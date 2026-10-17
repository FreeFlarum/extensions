<?php

namespace FoskyM\CustomLevels\Api\Resource;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Api\Sort\SortColumn;
use FoskyM\CustomLevels\Model\ExpLog;
use Illuminate\Database\Eloquent\Builder;
use Tobyz\JsonApiServer\Context;

/**
 * @extends Resource\AbstractDatabaseResource<ExpLog>
 */
class ExpLogResource extends Resource\AbstractDatabaseResource
{
    public function type(): string
    {
        return 'exp-logs';
    }

    public function model(): string
    {
        return ExpLog::class;
    }

    public function scope(Builder $query, Context $context): void
    {
        $query->where('user_id', $context->getActor()->id);
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->authenticated()
                ->defaultSort('-id')
                ->paginate(10),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Integer::make('user_id'),
            Schema\Integer::make('exp'),
            Schema\Integer::make('old_exp'),
            Schema\Integer::make('new_exp'),
            Schema\Str::make('type'),
            Schema\Arr::make('relationship'),
            Schema\DateTime::make('created_at'),
        ];
    }

    public function sorts(): array
    {
        return [
            SortColumn::make('id'),
        ];
    }
}
