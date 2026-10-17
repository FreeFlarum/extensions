import Widgets from 'ext:fof/forum-widgets-core/common/extend/Widgets';

import YoutubeFeedWidget from './components/YoutubeFeedWidget';

export default function (app) {
  new Widgets()
    .add({
      key: 'YtFeed',
      component: YoutubeFeedWidget,
      isDisabled: false,
      isUnique: true,
      placement: 'end',
      position: 1,
    })
    .extend(app, 'justoverclock-youtube-video-feed');
}
