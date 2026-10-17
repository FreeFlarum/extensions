/*
 * This file is part of justoverclock/last-tweet.
 *
 * Copyright (c) 2021 Marco Colia.
 * https://flarum.it
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
import app from 'flarum/common/app';
import Widget from 'ext:fof/forum-widgets-core/common/components/Widget';

export default class YoutubeFeedWidget extends Widget {
  oncreate(vnode) {
    var reqURL = 'https://api.rss2json.com/v1/api.json?rss_url=' + encodeURIComponent('https://www.youtube.com/feeds/videos.xml?channel_id=');

    function loadVideo(iframe) {
      $.getJSON(reqURL + iframe.getAttribute('cid'))
        .done(function (data) {
          var videoNumber = iframe.getAttribute('vnum') ? Number(iframe.getAttribute('vnum')) : 0;
          var item = data && data.items && data.items[videoNumber];
          if (!item) {
            iframe.style.display = 'none';
            return;
          }
          var link = item.link;
          var id = link.substr(link.indexOf('=') + 1);
          iframe.setAttribute('src', 'https://youtube.com/embed/' + id + '?controls=1&autoplay=0');
        })
        .fail(function () {
          iframe.style.display = 'none';
        });
    }

    var iframes = document.getElementsByClassName('latestVideoEmbed');
    for (var i = 0, len = iframes.length; i < len; i++) {
      loadVideo(iframes[i]);
    }
  }

  className() {
    return 'YtFeedWidget';
  }

  icon() {
    // Widget icon.
    return 'fab fa-youtube';
  }

  title() {
    if (app.forum.attribute('justoverclock-youtube-video-feed.showTitle') === true) {
      return app.translator.trans('justoverclock-youtube-video-feed.forum.title');
    }
  }

  content() {
    const ytWidth = app.forum.attribute('justoverclock-youtube-video-feed.ytWidth') || '321';
    const ytHeight = app.forum.attribute('justoverclock-youtube-video-feed.ytHeight') || '160';
    const ytChannelId = app.forum.attribute('justoverclock-youtube-video-feed.ytChannelId') || 'UCc7SyfF1ioDP2QwYGe0S-pg';
    return (
      <div class="youtube-feed">
        <iframe class="latestVideoEmbed" vnum="0" cid={ytChannelId} width={ytWidth} height={ytHeight} frameborder="0" allowfullscreen></iframe>
        <iframe class="latestVideoEmbed" vnum="1" cid={ytChannelId} width={ytWidth} height={ytHeight} frameborder="0" allowfullscreen></iframe>
        <iframe class="latestVideoEmbed" vnum="2" cid={ytChannelId} width={ytWidth} height={ytHeight} frameborder="0" allowfullscreen></iframe>
      </div>
    );
  }
}
