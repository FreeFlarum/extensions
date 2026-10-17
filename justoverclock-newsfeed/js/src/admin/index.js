/*
 * This file is part of justoverclock/flarum-ext-newsfeed.
 *
 * Copyright (c) 2021 Marco Colia.
 * https://flarum.it
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

import app from 'flarum/admin/app';

app.initializers.add('justoverclock/flarum-ext-newsfeed', () => {
  app.registry.for('justoverclock-newsfeed').registerSetting({
    setting: 'justoverclock-newsfeed.FeedUrl',
    name: 'FeedUrl',
    type: 'url',
    label: app.translator.trans('flarum-ext-newsfeed.admin.feedutltitle'),
    help: app.translator.trans('flarum-ext-newsfeed.admin.feedurldesc'),
    placeholder: 'Url of your Rss Feed',
  });
});
