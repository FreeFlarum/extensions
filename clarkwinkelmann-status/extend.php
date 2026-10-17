<?php

namespace ClarkWinkelmann\Status;

use Flarum\Api\Resource\UserResource;
use Flarum\Extend;
use Flarum\User\User;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\ApiResource(UserResource::class))
        ->fields(UserAttributes::class),

    (new Extend\Settings())
        ->serializeToForum('clarkwinkelmannStatusOnlyCountries', 'clarkwinkelmann-status.onlyCountries', 'boolval')
        ->serializeToForum('clarkwinkelmannStatusEnableText', 'clarkwinkelmann-status.enableText', 'boolval'),

    (new Extend\Policy())
        ->modelPolicy(User::class, Policies\UserPolicy::class),
];
