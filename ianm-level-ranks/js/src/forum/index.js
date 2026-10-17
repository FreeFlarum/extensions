import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import PostUser from 'flarum/forum/components/PostUser';
import LevelBar from './components/LevelBar';

app.initializers.add('ianm-level-ranks', () => {
  extend(PostUser.prototype, 'userViewItems', function (items, user) {
    items.add('levelBar', <LevelBar user={user} />, 0);
  });
});
