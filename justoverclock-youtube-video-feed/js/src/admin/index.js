import app from 'flarum/admin/app';
import registerWidget from '../common/registerWidget';

app.initializers.add('justoverclock/youtube-video-feed', () => {
  registerWidget(app);
  app.registry
    .for('justoverclock-youtube-video-feed')
    .registerSetting({
      setting: 'justoverclock-youtube-video-feed.ytWidth',
      name: 'justoverclock-youtube-video-feed.ytWidth',
      label: app.translator.trans('justoverclock-youtube-video-feed.admin.ytWidth'),
      help: app.translator.trans('justoverclock-youtube-video-feed.admin.ytWidth-help'),
      type: 'number',
    })
    .registerSetting({
      setting: 'justoverclock-youtube-video-feed.ytHeight',
      name: 'justoverclock-youtube-video-feed.ytHeight',
      label: app.translator.trans('justoverclock-youtube-video-feed.admin.ytHeight'),
      help: app.translator.trans('justoverclock-youtube-video-feed.admin.ytHeight-help'),
      type: 'number',
    })
    .registerSetting({
      setting: 'justoverclock-youtube-video-feed.ytChannelId',
      name: 'justoverclock-youtube-video-feed.ytChannelId',
      label: app.translator.trans('justoverclock-youtube-video-feed.admin.ytChannelId'),
      help: app.translator.trans('justoverclock-youtube-video-feed.admin.ytChannelId-help'),
      type: 'text',
    })
    .registerSetting({
      setting: 'justoverclock-youtube-video-feed.showTitle',
      name: 'justoverclock-youtube-video-feed.showTitle',
      label: app.translator.trans('justoverclock-youtube-video-feed.admin.showTitle'),
      type: 'boolean',
    });
});
