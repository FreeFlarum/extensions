import Extend from 'flarum/common/extenders';
import Post from 'flarum/common/models/Post';
import User from 'flarum/common/models/User';
import UserPageResolver from 'flarum/forum/resolvers/UserPageResolver';
import DislikesUserPage from './components/DislikesUserPage';
import PostDislikedNotification from './components/PostDislikedNotification';

export default [
  new Extend.Routes() //
    .add('user.dislikes', '/u/:username/dislikes', DislikesUserPage, UserPageResolver),

  new Extend.Notification() //
    .add('postDisliked', PostDislikedNotification),

  new Extend.Model(Post) //
    .hasMany<User>('dislikes')
    .attribute<number>('dislikesCount')
    .attribute<boolean>('canDislike'),
];
