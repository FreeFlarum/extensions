import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import WelcomeHero from 'flarum/forum/components/WelcomeHero';
import Button from 'flarum/common/components/Button';

export default function addLoginAndSignup() {
  // Only allow logged in users to hide the WelcomeHero
  // Add signup and login buttons to the WelcomeHero
  extend(WelcomeHero.prototype, 'bodyItems', function (items) {
    if (app.session?.user) return;

    items.remove('dismiss-button');

    const extraDivClassName = !!app.forum.attribute('blomstra.welcome-login.only-mobile') ? 'extraSignin extraSigninMobile' : 'extraSignin';

    items.add(
      'extraSignin',
      <div class={extraDivClassName}>
        <br />
        {!!app.forum.attribute('allowSignUp') ? (
          <Button className="Button" onclick={() => app.modal.show(() => import('flarum/forum/components/SignUpModal'))}>
            {app.translator.trans('core.forum.header.sign_up_link')}
          </Button>
        ) : (
          ''
        )}

        <Button className="Button Button--primary" onclick={() => app.modal.show(() => import('flarum/forum/components/LogInModal'))}>
          {app.translator.trans('core.forum.header.log_in_link')}
        </Button>
      </div>,
      70
    );
  });

  // If there's no logged in user, always show the WelcomeHero
  override(WelcomeHero.prototype, 'isHidden', function (original) {
    if (!app.session?.user) return false;
    return original();
  });
}
