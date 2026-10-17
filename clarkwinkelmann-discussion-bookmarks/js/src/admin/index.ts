import app from 'flarum/admin/app';

app.initializers.add('clarkwinkelmann-discussion-bookmarks', () => {
  app.registry.for('clarkwinkelmann-discussion-bookmarks').registerSetting({
    setting: 'discussion-bookmarks.independentButton',
    label: app.translator.trans('clarkwinkelmann-discussion-bookmarks.admin.settings.independentButton'),
    type: 'boolean',
  });
});
