<?php

namespace ClarkWinkelmann\UsernameBlacklist;

use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Extend;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/resources/less/admin.less'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\ApiResource(UserResource::class))
        ->field('username', function (Schema\Str $field) {
            return $field->rule(fn () => resolve(WhitelistRule::class));
        }),
];
