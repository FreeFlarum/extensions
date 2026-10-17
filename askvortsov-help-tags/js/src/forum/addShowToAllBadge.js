import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Discussion from 'flarum/common/models/Discussion';
import Badge from 'flarum/common/components/Badge';

export default function addShowToAllBadge() {
  extend(Discussion.prototype, 'badges', function (badges) {
    if (this.showToAll()) {
      badges.add(
        'showToAll',
        <Badge type="showToAll" label={app.translator.trans('askvortsov-help-tags.forum.badge.show_to_all_tooltip')} icon="fas fa-eye" />,
        10
      );
    }
  });
}
