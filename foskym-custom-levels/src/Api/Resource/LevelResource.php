<?php

namespace FoskyM\CustomLevels\Api\Resource;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use FoskyM\CustomLevels\Model\Level;
use Tobyz\JsonApiServer\Context;

/**
 * @extends Resource\AbstractDatabaseResource<Level>
 */
class LevelResource extends Resource\AbstractDatabaseResource
{
    public function type(): string
    {
        return 'levels';
    }

    public function model(): string
    {
        return Level::class;
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Create::make()
                ->admin(),
            Endpoint\Update::make()
                ->admin(),
            Endpoint\Delete::make()
                ->admin(),
            Endpoint\Index::make()
                ->admin(),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('name')
                ->requiredOnCreate()
                ->maxLength(255)
                ->writable(),
            Schema\Integer::make('min_exp_required')
                ->default(0)
                ->writable(),
        ];
    }

    public function createAction(object $model, Context $context): object
    {
        return Level::where('name', $model->name)->first() ?? parent::createAction($model, $context);
    }
}
