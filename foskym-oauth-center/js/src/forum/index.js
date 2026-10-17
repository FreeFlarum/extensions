import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import UserPage from 'flarum/forum/components/UserPage';
import LinkButton from 'flarum/common/components/LinkButton';

export { default as extend } from './extend';

app.initializers.add('foskym/flarum-oauth-center', () => {
  extend(UserPage.prototype, 'navItems', function (items) {
    if (app.session.user && app.session.user.id() === this.user.id()) {
      items.add(
        'authorized',
        LinkButton.component(
          {
            href: app.route('user.authorized', { username: this.user.slug() }),
            icon: 'fas fa-user-friends',
          },
          [
            app.translator.trans('foskym-oauth-center.forum.page.label.authorized'),
          ]
        ),
        -110
      );
    }
  });
});
