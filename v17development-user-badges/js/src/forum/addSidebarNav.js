import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import LinkButton from 'flarum/common/components/LinkButton';

export default function addSidebarNav() {
  extend(IndexSidebar.prototype, 'navItems', function (items) {
    items.add(
      'badges',
      <LinkButton icon="fas fa-id-badge" href={app.route('badges')}>
        {app.translator.trans('v17development-flarum-badges.forum.badge.badges')}
      </LinkButton>,
      15
    );

    return items;
  });
}
