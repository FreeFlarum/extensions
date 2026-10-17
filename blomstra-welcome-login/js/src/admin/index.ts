import app from 'flarum/admin/app';

app.initializers.add('blomstra/welcome-login', () => {
  app.registry.for('blomstra-welcome-login').registerSetting({
    label: app.translator.trans('blomstra-welcome-login.admin.settings.welcome-only-mobile.label'),
    setting: 'blomstra.welcome-login.only-mobile',
    type: 'boolean',
    help: app.translator.trans('blomstra-welcome-login.admin.settings.welcome-only-mobile.help'),
  });
});
