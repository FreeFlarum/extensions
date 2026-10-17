<?php

/*
 * This file is part of serakoi/flarumstaffbadge.
 *
 * Copyright (c) 2021 Serakoi.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Serakoi\FlarumStaffBadge;

use Flarum\Api\Context;
use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\User\User;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),
    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\ApiResource(UserResource::class))
        ->fields(fn () => [
            Schema\Str::make('staffBadge')
                ->property('staffBadge')
                ->nullable()
                ->writable(fn (User $user, Context $context) => $context->getActor()->isAdmin())
                //? Set staff badge to true or false
                ->set(fn (User $user, ?string $value) => $user->staffBadge = $value === 'true' ? 'true' : 'false'),
            Schema\Str::make('tagList')
                ->property('tagList')
                ->nullable()
                ->writable(fn (User $user, Context $context) => $context->getActor()->isAdmin()),
        ]),

    (new Extend\Settings)
        ->serializeToForum('staffBadgeTitle', 'serakoi-flarumstaffbadge.staffBadge')
        ->serializeToForum('staffBadgeColor', 'serakoi-flarumstaffbadge.staffBadgeTextColor')
        ->serializeToForum('staffBadgeBg', 'serakoi-flarumstaffbadge.staffBadgeTextBg'),
];
