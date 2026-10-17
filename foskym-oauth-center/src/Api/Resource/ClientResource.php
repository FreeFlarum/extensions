<?php

namespace FoskyM\OAuthCenter\Api\Resource;

use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use FoskyM\OAuthCenter\Models\Client;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends AbstractDatabaseResource<Client>
 */
class ClientResource extends AbstractDatabaseResource
{
    public function type(): string
    {
        return 'oauth-clients';
    }

    public function model(): string
    {
        return Client::class;
    }

    public function newModel(OriginalContext $context): object
    {
        $client = parent::newModel($context);

        if ($context->creating(self::class)) {
            $client->user_id = $context->getActor()->id;
        }

        return $client;
    }

    public function find(string $id, OriginalContext $context): ?object
    {
        if ($context->showing(self::class)) {
            return $this->query($context)->where('client_id', $id)->first();
        }

        return parent::find($id, $context);
    }

    public function create(object $model, OriginalContext $context): object
    {
        return Client::where('client_id', $model->client_id)->first() ?? parent::create($model, $context);
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->admin(),
            Endpoint\Show::make()
                ->authenticated()
                ->can('foskym-oauth-center.use-oauth'),
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
        $isAdmin = fn (Client $client, Context $context) => $context->getActor()->isAdmin();
        $emptyToNull = fn (string $attribute) => function (Client $client, ?string $value) use ($attribute) {
            $client->$attribute = $value === '' ? null : $value;
        };

        return [
            Schema\Str::make('client_id')
                ->writable()
                ->nullable(),
            Schema\Str::make('client_secret')
                ->visible($isAdmin)
                ->writable()
                ->nullable(),
            Schema\Str::make('redirect_uri')
                ->writableOnUpdate()
                ->nullable(),
            Schema\Str::make('grant_types')
                ->writableOnUpdate()
                ->nullable()
                ->set($emptyToNull('grant_types')),
            Schema\Str::make('scope')
                ->writableOnUpdate()
                ->nullable()
                ->set($emptyToNull('scope')),
            Schema\Str::make('user_id')
                ->visible($isAdmin)
                ->nullable(),
            Schema\Str::make('client_name')
                ->writableOnUpdate()
                ->nullable(),
            Schema\Str::make('client_icon')
                ->writableOnUpdate()
                ->nullable(),
            Schema\Str::make('client_desc')
                ->writableOnUpdate()
                ->nullable(),
            Schema\Str::make('client_home')
                ->writableOnUpdate()
                ->nullable(),
        ];
    }
}
