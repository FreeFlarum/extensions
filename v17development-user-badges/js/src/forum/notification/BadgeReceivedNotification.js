import app from 'flarum/forum/app';
import Notification from 'flarum/forum/components/Notification';
import Icon from 'flarum/common/components/Icon';

export default class BadgeReceivedNotification extends Notification {
  icon() {
    return '';
  }

  href() {
    return app.route('user.badges', {
      username: app.session.user.slug(),
    });
  }

  content() {
    return app.translator.trans('v17development-flarum-badges.forum.notification.title');
  }

  excerpt() {
    const badge = this.attrs.notification.subject().badge();

    return (
      <div>
        {badge.icon() && <Icon name={badge.icon()} />}

        {badge.name()}
      </div>
    );
  }
}
