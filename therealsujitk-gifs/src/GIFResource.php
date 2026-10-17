<?php

namespace Therealsujitk\GIFs;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Api\Sort\SortColumn;
use Illuminate\Database\Eloquent\Builder;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends Resource\AbstractDatabaseResource<GIF>
 */
class GIFResource extends Resource\AbstractDatabaseResource
{
    public function type(): string
    {
        return 'therealsujitk-gifs';
    }

    public function model(): string
    {
        return GIF::class;
    }

    public function scope(Builder $query, OriginalContext $context): void
    {
        $query->where('user_id', $context->getActor()->id);
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->defaultSort('-id'),
            Endpoint\Create::make()
                ->authenticated(),
            Endpoint\Delete::make()
                ->authenticated(),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('gifID')
                ->property('gif_id')
                ->requiredOnCreate()
                ->maxLength(255)
                ->writableOnCreate(),

            Schema\Relationship\ToOne::make('user')
                ->type('users')
                ->includable(),
        ];
    }

    public function sorts(): array
    {
        return [
            SortColumn::make('id'),
        ];
    }

    /**
     * Favourites are deleted by their GIF ID, as in v1.
     */
    public function find(string $id, OriginalContext $context): ?object
    {
        return $this->query($context)->where('gif_id', $id)->first();
    }

    public function creating(object $model, OriginalContext $context): ?object
    {
        $model->user()->associate($context->getActor());

        return $model;
    }
}
