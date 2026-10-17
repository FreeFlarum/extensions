<?php

namespace FoskyM\OAuthCenter\Api\Resource;

use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use FoskyM\OAuthCenter\Models\Scope;
use Illuminate\Database\Eloquent\Builder;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends AbstractDatabaseResource<Scope>
 */
class ScopeResource extends AbstractDatabaseResource
{
    public function type(): string
    {
        return 'oauth-scopes';
    }

    public function model(): string
    {
        return Scope::class;
    }

    public function scope(Builder $query, OriginalContext $context): void
    {
        $actor = $context->getActor();

        if (! $actor->isAdmin() && ! $actor->hasPermission('foskym-oauth-center.use-oauth')) {
            $query->whereRaw('1 = 0');
        }
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->authenticated(),
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
        $isAdmin = fn (Scope $scope, Context $context) => $context->getActor()->isAdmin();

        return [
            Schema\Str::make('scope')
                ->writable()
                ->nullable(),
            Schema\Str::make('resource_path')
                ->visible($isAdmin)
                ->writable()
                ->nullable(),
            Schema\Str::make('method')
                ->visible($isAdmin)
                ->writable()
                ->nullable(),
            Schema\Str::make('visible_fields')
                ->visible($isAdmin)
                ->writableOnUpdate()
                ->nullable(),
            Schema\Attribute::make('is_default')
                ->writableOnUpdate()
                ->nullable()
                ->set(fn (Scope $scope, mixed $value) => $scope->is_default = $value ? 1 : 0),
            Schema\Str::make('scope_name')
                ->writableOnUpdate()
                ->nullable(),
            Schema\Str::make('scope_icon')
                ->writableOnUpdate()
                ->nullable(),
            Schema\Str::make('scope_desc')
                ->writableOnUpdate()
                ->nullable(),
        ];
    }
}
