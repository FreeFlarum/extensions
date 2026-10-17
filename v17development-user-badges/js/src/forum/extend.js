import Extend from 'flarum/common/extenders';
import User from 'flarum/common/models/User';
import commonExtend from '../common/extend';
import BadgesProfilePage from './components/BadgesProfilePage';
import BadgesOverviewPage from './components/BadgesOverviewPage';
import BadgeItemPage from './components/BadgeItemPage';
import BadgeReceivedNotification from './notification/BadgeReceivedNotification';

export default [
  ...commonExtend,

  new Extend.Model(User) //
    .hasMany('userBadges')
    .hasOne('userPrimaryBadge'),

  new Extend.Routes()
    // Add user badges to the user profile
    .add('user.badges', '/u/:username/badges', BadgesProfilePage)
    // Badges overview page
    .add('badges', '/badges', BadgesOverviewPage)
    // Badge item page
    .add('badges.item', '/badges/:id', BadgeItemPage),

  // Badge received notification
  new Extend.Notification() //
    .add('badgeReceived', BadgeReceivedNotification),
];
