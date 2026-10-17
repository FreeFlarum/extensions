<?php

/*
 * This file is part of datlechin/flarum-oauth-spotify.
 *
 * Copyright (c) 2023 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\OAuthSpotify;

use Datlechin\OAuthSpotify\Providers\Spotify;
use Flarum\Extend;
use FoF\OAuth\Extend\RegisterProvider;

return [

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->css(__DIR__ . '/less/forum.less'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new RegisterProvider(Spotify::class)),
];
