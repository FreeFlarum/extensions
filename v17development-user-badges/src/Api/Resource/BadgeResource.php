<?php

namespace V17Development\FlarumUserBadges\Api\Resource;

use Carbon\Carbon;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Illuminate\Database\Eloquent\Builder;
use Tobyz\JsonApiServer\Context as OriginalContext;
use V17Development\FlarumUserBadges\Badge\Badge;
use V17Development\FlarumUserBadges\Badge\BadgeValidator;

/**
 * @extends AbstractDatabaseResource<Badge>
 */
class BadgeResource extends AbstractDatabaseResource
{
    public function __construct(
        protected BadgeValidator $validator
    ) {
    }

    public function type(): string
    {
        return 'badges';
    }

    public function model(): string
    {
        return Badge::class;
    }

    public function scope(Builder $query, OriginalContext $context): void
    {
        // Only show uncategorized badges
        if ($context->listing(self::class) && $context->queryParam('uncategorized')) {
            $query->whereNull('badge_category_id');
        }
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->defaultInclude(['category']),
            Endpoint\Show::make()
                ->defaultInclude(['category']),
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
            Schema\Str::make('icon')
                ->writable()
                ->nullable(),
            Schema\Integer::make('order')
                ->writable(),
            Schema\Str::make('image')
                ->writable()
                ->nullable(),
            Schema\Str::make('description')
                ->writable()
                ->nullable(),
            Schema\Integer::make('earnedAmount')
                ->get(fn (Badge $badge) => $badge->earnedCount()),
            Schema\Boolean::make('isVisible')
                ->property('is_visible')
                ->writable(),
            Schema\Str::make('backgroundColor')
                ->property('background_color')
                ->writable()
                ->nullable(),
            Schema\Str::make('iconColor')
                ->property('icon_color')
                ->writable()
                ->nullable(),
            Schema\Str::make('labelColor')
                ->property('label_color')
                ->writable()
                ->nullable(),
            Schema\DateTime::make('createdAt')
                ->property('created_at'),

            Schema\Relationship\ToOne::make('category')
                ->type('badgeCategories')
                ->includable(),
            Schema\Relationship\ToMany::make('users')
                ->type('userBadges')
                ->includable(),
        ];
    }

    public function creating(object $model, OriginalContext $context): ?object
    {
        $model->order ??= 0;
        $model->is_visible ??= true;
        $model->created_at = Carbon::now();

        return $model;
    }

    public function saving(object $model, OriginalContext $context): ?object
    {
        $this->validator->assertValid($model->getDirty());

        return $model;
    }
}
