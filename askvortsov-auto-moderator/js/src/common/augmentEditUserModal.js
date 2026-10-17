import { extend, override } from 'flarum/common/extend';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import ItemList from 'flarum/common/utils/ItemList';
import managedGroups from './utils/managedGroups';
import ManagedGroups from './components/ManagedGroups';

export default function augmentEditUserModal() {
  extend('flarum/common/components/EditUserModal', 'oninit', function () {
    if (!app.session.user.isAdmin()) return;

    this.loading = true;
    app.store.find('criteria').then((criteria) => {
      const managed = managedGroups(criteria);
      this.availableGroups = this.availableGroups.filter((group) => !managed.includes(group));

      this.loading = false;
      m.redraw();
    });
  });

  override('flarum/common/components/EditUserModal', 'fields', function (original) {
    if (this.loading) {
      const items = new ItemList();
      items.add('loading', <LoadingIndicator></LoadingIndicator>);
      return items;
    }

    const items = original();

    if (app.session.user.isAdmin()) {
      items.add('Criteria', <ManagedGroups criteria={app.store.all('criteria')} user={this.attrs.user} />, 10);
    }

    return items;
  });
}
