import app from 'flarum/admin/app';
import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import Icon from 'flarum/common/components/Icon';
import saveSettings from 'flarum/admin/utils/saveSettings';

export default class InstallAutoModerationMessage extends Component {
  view() {
    // Message is dismissed
    if (typeof app.data.settings.badges_install_automoderation !== 'undefined') {
      return null;
    }

    const extensionInstalled = typeof app.data.extensions['askvortsov-auto-moderator'] !== 'undefined';

    return (
      <div className={'FlarumBadges-install-dependency'}>
        <div className={'FlarumBadges-install-dependency-title'}>
          {app.translator.trans('v17development-flarum-badges.admin.auto_moderator.not_installed.title')}
        </div>
        <div className={'FlarumBadges-install-dependency-content'}>
          {app.translator.trans('v17development-flarum-badges.admin.auto_moderator.not_installed.content', {
            a: <a href={'https://discuss.flarum.org/d/27306'} target={'_blank'} />,
          })}
        </div>
        <div className={'FlarumBadges-install-dependency-buttons'}>
          <a
            className={'Button'}
            href={extensionInstalled ? '#/extension/askvortsov-auto-moderator' : 'https://discuss.flarum.org/d/27306'}
            target={extensionInstalled ? '' : '_blank'}
          >
            <Icon name={`fas ${extensionInstalled ? 'fa-robot' : 'fa-download'}`} className="Button-icon" />

            <span class="Button-label">
              {app.translator.trans(
                extensionInstalled ? 'core.admin.extension.open_modal' : 'v17development-flarum-badges.admin.auto_moderator.not_installed.install'
              )}
            </span>
          </a>
          <Button
            className={'Button'}
            onclick={() =>
              saveSettings({
                badges_install_automoderation: 'dismissed',
              })
            }
            icon={'fas fa-times'}
          >
            {app.translator.trans('v17development-flarum-badges.admin.auto_moderator.not_installed.dismiss')}
          </Button>
        </div>
      </div>
    );
  }
}
