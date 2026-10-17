<?php

/*
 * This file is part of therealsujitk/flarum-ext-gifs.
 *
 * Copyright (c) Sujit Kumar.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Therealsujitk\GIFs;

use Flarum\Api\Resource\ForumResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\Settings\SettingsRepositoryInterface;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),
        
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),
    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\ApiResource(ForumResource::class))
        ->fields(fn () => [
            Schema\Str::make('therealsujitk-gifs.engine')
                ->get(fn () => resolve(SettingsRepositoryInterface::class)->get('therealsujitk-gifs.engine', 'giphy')),
            Schema\Str::make('therealsujitk-gifs.api_key')
                ->nullable()
                ->get(fn () => resolve(SettingsRepositoryInterface::class)->get('therealsujitk-gifs.api_key', null)),
            Schema\Str::make('therealsujitk-gifs.rating')
                ->get(fn () => resolve(SettingsRepositoryInterface::class)->get('therealsujitk-gifs.rating', 'off')),
        ]),

    new Extend\ApiResource(GIFResource::class),
];
