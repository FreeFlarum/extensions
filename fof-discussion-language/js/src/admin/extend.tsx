import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import SettingDropdown from 'flarum/admin/components/SettingDropdown';
import commonExtend from '../common/extend';
import LanguagesSettingsPage from './components/LanguagesSettingsPage';

export default [
  ...commonExtend,

  new Extend.Admin()
    .page(LanguagesSettingsPage)
    .permission(
      () => ({
        permission: 'discussion.changeLanguageModerate',
        icon: 'fas fa-globe',
        label: app.translator.trans('fof-discussion-language.admin.permissions.allow_change_language_label'),
      }),
      'moderate',
      65
    )
    .permission(
      () => ({
        id: 'fof-discussion-language.allow_language_change',
        icon: 'fas fa-globe',
        label: app.translator.trans('fof-discussion-language.admin.permissions.allow_change_language_label'),
        setting: () => {
          const minutes = parseInt(app.data.settings['fof-discussion-language.allow_language_change']);

          return (
            <SettingDropdown
              defaultLabel={
                minutes
                  ? app.translator.trans('core.admin.permissions_controls.allow_some_minutes_button', { count: minutes })
                  : app.translator.trans('core.admin.permissions_controls.allow_indefinitely_button')
              }
              setting="fof-discussion-language.allow_language_change"
              default="10"
              options={[
                { value: '-1', label: app.translator.trans('core.admin.permissions_controls.allow_indefinitely_button') },
                { value: '10', label: app.translator.trans('core.admin.permissions_controls.allow_ten_minutes_button') },
                { value: 'reply', label: app.translator.trans('core.admin.permissions_controls.allow_until_reply_button') },
              ]}
            />
          );
        },
      }),
      'start',
      65
    ),
];
