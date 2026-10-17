import app from 'flarum/forum/app';
import UserPage from 'flarum/forum/components/UserPage';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import UserBadgeList from './UserBadgeList';

export default class BadgesProfilePage extends UserPage {
  oninit(vnode) {
    super.oninit(vnode);

    this.loading = true;

    this.loadUser(m.route.param('username'));
  }

  content() {
    if (!this.user || this.loading) {
      return <LoadingIndicator size={'large'} />;
    }

    return UserBadgeList.component({
      user: this.user,
    });
  }

  show(user) {
    super.show(user);

    app.store
      .find('users', user.id(), {
        include: 'userBadges,userBadges.badge,userBadges.badge.category',
      })
      .then(() => {
        this.loading = false;
        m.redraw();
      });
  }
}
