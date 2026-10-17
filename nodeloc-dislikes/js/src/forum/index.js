import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';

import addDislikeAction from './addDislikeAction';
import addDislikesList from './addDislikesList';
import addDislikesTabToUserProfile from './addDislikesTabToUserProfile';

export { default as extend } from './extend';

app.initializers.add('nodeloc-dislikes', () => {
  addDislikeAction();
  addDislikesList();
  addDislikesTabToUserProfile();

  extend('flarum/forum/components/NotificationGrid', 'notificationTypes', function (items) {
    items.add('postDisliked', {
      name: 'postDisliked',
      icon: 'far fa-thumbs-down',
      label: app.translator.trans('nodeloc-dislikes.forum.settings.notify_post_disliked_label'),
    });
  });
});
