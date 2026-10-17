<?php

namespace V17Development\FlarumUserBadges\Api\Resource;

use Carbon\Carbon;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Tobyz\JsonApiServer\Context as OriginalContext;
use V17Development\FlarumUserBadges\BadgeCategory\BadgeCategory;
use V17Development\FlarumUserBadges\BadgeCategory\BadgeCategoryValidator;

/**
 * @extends AbstractDatabaseResource<BadgeCategory>
 */
class BadgeCategoryResource extends AbstractDatabaseResource
{
    public function __construct(
        protected BadgeCategoryValidator $validator
    ) {
    }

    public function type(): string
    {
        return 'badgeCategories';
    }

    public function model(): string
    {
        return BadgeCategory::class;
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->defaultInclude(['badges']),
            Endpoint\Create::make()
                ->admin(),
            Endpoint\Update::make()
                ->admin(),
            Endpoint\Delete::make()
                ->admin(),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('name')
                ->requiredOnCreate()
                ->writable(),
            Schema\Str::make('description')
                ->writable()
                ->nullable(),
            Schema\Integer::make('order'),
            Schema\Boolean::make('isEnabled')
                ->property('is_enabled')
                ->writable(),
            Schema\Boolean::make('isTable')
                ->property('is_table')
                ->writable(),
            Schema\DateTime::make('createdAt')
                ->property('created_at'),

            Schema\Relationship\ToMany::make('badges')
                ->type('badges')
                ->includable(),
        ];
    }

    public function creating(object $model, OriginalContext $context): ?object
    {
        $model->order = 0;
        $model->is_enabled ??= true;
        $model->is_table ??= true;
        $model->created_at = Carbon::now();

        return $model;
    }

    public function saving(object $model, OriginalContext $context): ?object
    {
        $this->validator->assertValid($model->getDirty());

        return $model;
    }
}
