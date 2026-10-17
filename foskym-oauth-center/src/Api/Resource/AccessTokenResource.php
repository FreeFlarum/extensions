<?php

namespace FoskyM\OAuthCenter\Api\Resource;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use FoskyM\OAuthCenter\Models\AccessToken;
use FoskyM\OAuthCenter\Models\AuthorizationCode;
use FoskyM\OAuthCenter\Models\RefreshToken;
use Laminas\Diactoros\Response\EmptyResponse;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends AbstractDatabaseResource<AccessToken>
 */
class AccessTokenResource extends AbstractDatabaseResource
{
    public function type(): string
    {
        return 'oauth-tokens';
    }

    public function model(): string
    {
        return AccessToken::class;
    }

    public function newModel(OriginalContext $context): object
    {
        $token = parent::newModel($context);

        if ($context->creating(self::class)) {
            $token->expires = date('Y-m-d H:i:s', time() + 3600 * 24);
        }

        return $token;
    }

    public function create(object $model, OriginalContext $context): object
    {
        return AccessToken::where('access_token', $model->access_token)->first() ?? parent::create($model, $context);
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Create::make()
                ->admin(),
            Endpoint\Endpoint::make('deleteExpired')
                ->route('DELETE', '/expired')
                ->admin()
                ->action(function () {
                    $now = date('Y-m-d H:i:s');

                    AccessToken::where('expires', '<', $now)->delete();
                    RefreshToken::where('expires', '<', $now)->delete();
                    AuthorizationCode::where('expires', '<', $now)->delete();

                    return null;
                })
                ->response(fn () => new EmptyResponse()),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('access_token')
                ->writableOnCreate()
                ->requiredOnCreate(),
            Schema\Str::make('client_id')
                ->writableOnCreate()
                ->requiredOnCreate(),
            Schema\Str::make('user_id')
                ->writableOnCreate()
                ->requiredOnCreate(),
            Schema\Str::make('expires')
                ->writableOnCreate()
                ->nullable()
                ->set(function (AccessToken $token, ?string $value) {
                    if ($time = strtotime((string) $value)) {
                        $token->expires = date('Y-m-d H:i:s', $time);
                    }
                }),
            Schema\Str::make('scope')
                ->writableOnCreate()
                ->requiredOnCreate(),
        ];
    }
}
