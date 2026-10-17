<?php

namespace FoskyM\OAuthCenter\Api\Resource;

use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Flarum\Api\Sort\SortColumn;
use Flarum\Settings\SettingsRepositoryInterface;
use FoskyM\OAuthCenter\Models\Record;
use Illuminate\Database\Eloquent\Builder;
use Laminas\Diactoros\Response\EmptyResponse;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends AbstractDatabaseResource<Record>
 */
class RecordResource extends AbstractDatabaseResource
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function type(): string
    {
        return 'oauth-records';
    }

    public function model(): string
    {
        return Record::class;
    }

    public function scope(Builder $query, OriginalContext $context): void
    {
        $query->where('user_id', $context->getActor()->id);
    }

    public function endpoints(): array
    {
        $allowDelete = fn () => (bool) $this->settings->get('foskym-oauth-center.allow_delete_records');

        return [
            Endpoint\Endpoint::make('deleteAll')
                ->route('DELETE', '/all')
                ->admin()
                ->action(function () {
                    Record::where('user_id', '!=', 0)->delete();

                    return null;
                })
                ->response(fn () => new EmptyResponse()),
            Endpoint\Endpoint::make('deleteUser')
                ->route('DELETE', '/user')
                ->authenticated()
                ->visible($allowDelete)
                ->action(function (Context $context) {
                    Record::where('user_id', $context->getActor()->id)->delete();

                    return null;
                })
                ->response(fn () => new EmptyResponse()),
            Endpoint\Index::make()
                ->authenticated()
                ->defaultSort('-authorized_at')
                ->paginate(10, 10),
            Endpoint\Delete::make()
                ->authenticated()
                ->visible($allowDelete),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Attribute::make('client')
                ->get(fn (Record $record) => $record->client?->makeHidden(['client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope', 'user_id'])->toArray()),
            Schema\Str::make('user_id'),
            Schema\Str::make('authorized_at'),
        ];
    }

    public function sorts(): array
    {
        return [
            SortColumn::make('authorized_at'),
        ];
    }
}
