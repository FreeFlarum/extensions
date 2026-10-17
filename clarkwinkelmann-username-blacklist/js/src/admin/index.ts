import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';

app.initializers.add('clarkwinkelmann-username-blacklist', () => {
  app.registry
    .for('clarkwinkelmann-username-blacklist')
    .registerSetting({
      setting: 'clarkwinkelmann-username-blacklist.regex',
      label: app.translator.trans('clarkwinkelmann-username-blacklist.admin.settings.regex'),
      help: app.translator.trans('clarkwinkelmann-username-blacklist.admin.settings.regexHelp'),
      type: 'boolean',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-username-blacklist.message',
      label: app.translator.trans('clarkwinkelmann-username-blacklist.admin.settings.message'),
      type: 'text',
    })
    .registerSetting(function (this: ExtensionPage) {
      return m('.Form-group', [
        m('label', app.translator.trans('clarkwinkelmann-username-blacklist.admin.settings.blacklist')),
        m('.helpText', app.translator.trans('clarkwinkelmann-username-blacklist.admin.settings.blacklistHelp')),
        m('textarea.FormControl', {
          bidi: this.setting('clarkwinkelmann-username-blacklist.blacklist'),
        }),
      ]);
    })
    .registerSetting(function (this: ExtensionPage) {
      return m('.Form-group', [
        m('label', app.translator.trans('clarkwinkelmann-username-blacklist.admin.settings.whitelist')),
        m('.helpText', app.translator.trans('clarkwinkelmann-username-blacklist.admin.settings.whitelistHelp')),
        m('textarea.FormControl', {
          bidi: this.setting('clarkwinkelmann-username-blacklist.whitelist'),
        }),
      ]);
    });
});
