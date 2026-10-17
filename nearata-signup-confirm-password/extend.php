<?php

namespace Nearata\SignUpConfirmPassword;

use Flarum\Api\Context;
use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\User\Event\Saving;
use Flarum\User\User;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\ApiResource(UserResource::class))
        ->fields(fn () => [
            Schema\Str::make('confirmPassword')
                ->visible(false)
                ->writable(fn (User $user, Context $context) => $context->creating())
                ->save(fn () => null),
        ]),

    (new Extend\Event)
        ->listen(Saving::class, ValidatePassword::class)
];
