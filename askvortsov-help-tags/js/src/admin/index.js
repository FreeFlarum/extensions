import app from 'flarum/admin/app';
import { override } from 'flarum/common/extend';

app.initializers.add('askvortsov/flarum-help-tags', () => {
  override(app, 'getRequiredPermissions', (original, permission) => {
    var required = original(permission);

    if (/(viewTag|startDiscussion|startWithoutApproval)$/.test(permission)) {
      return required.filter((a) => !/viewForum$/.test(a));
    }

    return required;
  });

  app.registry.for('askvortsov-help-tags').registerPermission(
    {
      icon: 'fas fa-eye',
      label: app.translator.trans('askvortsov-help-tags.admin.permissions.view_tag_label'),
      permission: 'viewTag',
      tagScoped: true,
      allowGuest: true,
    },
    'view',
    100
  );
});
