import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import Page from 'flarum/common/components/Page';
import PageStructure from 'flarum/forum/components/PageStructure';
import WelcomeHero from 'flarum/forum/components/WelcomeHero';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import GroupBadge from 'flarum/common/components/GroupBadge';
import UserList from './UserList';

/* global app, m */

export default class GroupListPage extends Page {
  oninit(vnode) {
    super.oninit(vnode);

    this.items = null;

    app.store.find('clarkwinkelmann-group-list-items').then((items) => {
      this.items = items;
      m.redraw();
    });

    this.bodyClass = 'GroupList-page';
  }

  view() {
    return m(
      PageStructure,
      {
        className: 'IndexPage',
        hero: () => m(WelcomeHero),
        sidebar: () => m(IndexSidebar),
      },
      m('.GroupList-content', this.content())
    );
  }

  content() {
    if (this.items === null) {
      return LoadingIndicator.component();
    }

    return this.items.map((item) =>
      m('div', [
        m('h3.GroupList-title', [
          GroupBadge.component({
            group: item.group(),
          }),
          ' ',
          item.group().namePlural(),
        ]),
        item.contentHtml() ? m('.GroupList-description', m.trust(item.contentHtml())) : null,
        m(UserList, {
          users: item.members(),
          hideBadgeId: 'group' + item.group().id(),
        }),
      ])
    );
  }
}
