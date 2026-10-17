import app from 'flarum/admin/app';

app.initializers.add('akr-chevereto', (app) => {
  app.registry
    .for('akr-chevereto')
    .registerSetting({
      type: 'text',
      setting: 'akr-chevereto.url',
      label: app.translator.trans('akr-chevereto.admin.setting.url'),
    })
    .registerSetting({
      type: 'text',
      setting: 'akr-chevereto.insert_type',
      label: app.translator.trans('akr-chevereto.admin.setting.insert_type'),
    });
});
