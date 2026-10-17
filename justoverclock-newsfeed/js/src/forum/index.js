/*
 * This file is part of justoverclock/flarum-ext-newsfeed.
 *
 * Copyright (c) 2021 Marco Colia.
 * https://flarum.it
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

import { extend } from 'flarum/common/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import RSS from 'vanilla-rss';
import app from 'flarum/forum/app';

app.initializers.add('justoverclock/flarum-ext-newsfeed', () => {
  extend(IndexPage.prototype, 'view', function (vdom) {
    const hero = vdom.attrs.hero;
    vdom.attrs.hero = () => [hero && hero(), m('div', { className: 'NewsFeed', id: 'divRss' })];
  });

  extend(IndexPage.prototype, 'oncreate', function () {
    const FeedUrl = app.forum.attribute('FeedUrl');
    if (!FeedUrl) return;

    const rss = new RSS(document.querySelector('#divRss'), FeedUrl, {
      limit: 5,
      ssl: true,
      layoutTemplate: "<div class='feed-container'>{entries}</div>",
      entryTemplate:
        "<li class='licss'><a class='titleFeed' target='_blank' href=\"{url}\">{title}</a><br/>{shortBodyPlain}... -  <b>{author}</b></li>",
    });
    rss.render().then(() => {});
  });
});
