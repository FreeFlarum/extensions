import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import UserPage from 'flarum/forum/components/UserPage';
import UserControls from 'flarum/forum/utils/UserControls';
import LinkButton from 'flarum/common/components/LinkButton';
import Button from 'flarum/common/components/Button';
import DiscussionListState from 'flarum/forum/states/DiscussionListState';
import GiveBadgeModal from './components/GiveBadgeModal';
import addSidebarNav from './addSidebarNav';
import UserBadgeListState from './states/UserBadgeListState';
import addBadgeListUserCard from './addBadgeListUserCard';

export { default as extend } from './extend';

app.initializers.add('v17development-flarum-badges', () => {
  addSidebarNav();

  app.userBadgeListState = new UserBadgeListState({});

  // Enable badge notifications?
  extend('flarum/forum/components/NotificationGrid', 'notificationTypes', function (items) {
    items.add('badgeReceived', {
      name: 'badgeReceived',
      icon: 'fas fa-user-tag',
      label: app.translator.trans('v17development-flarum-badges.forum.notification.settings'),
    });
  });

  // Add uploads to user page menu items
  extend(UserPage.prototype, 'navItems', function (items) {
    items.add(
      'badges',
      LinkButton.component(
        {
          href: app.route('user.badges', {
            username: this.user.slug(),
          }),
          name: 'badges',
          icon: 'fas fa-user-tag',
        },
        [
          app.translator.trans('v17development-flarum-badges.forum.badge.badges'),
          <span className="Button-badge">{(this.user.userBadges() || []).length}</span>,
        ]
      ),
      90
    );
  });

  extend(UserControls, 'moderationControls', function (items, user) {
    // User can give badges
    if (app.forum.attribute('canGiveBadge')) {
      items.add(
        'test',
        <Button
          icon="fas fa-user-tag"
          onclick={() =>
            app.modal.show(GiveBadgeModal, {
              user: user,
            })
          }
        >
          {app.translator.trans('v17development-flarum-badges.forum.give_badge')}
        </Button>
      );
    }
  });

  addBadgeListUserCard();

  extend(DiscussionListState.prototype, 'requestParams', function (params) {
    params.include.push('user.userBadges', 'user.userBadges.badge');
  });
});
