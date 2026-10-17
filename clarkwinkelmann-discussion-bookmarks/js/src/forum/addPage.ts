import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import GlobalSearchState from 'flarum/forum/states/GlobalSearchState';
import DiscussionListState from 'flarum/forum/states/DiscussionListState';
import IndexPage from 'flarum/forum/components/IndexPage';
import LinkButton from 'flarum/common/components/LinkButton';

export default function () {
  extend(IndexSidebar.prototype, 'navItems', function (items) {
    if (!app.session.user) {
      return;
    }

    items.add(
      'discussion-bookmarks',
      LinkButton.component(
        {
          href: app.route('discussionBookmarks', app.search.state.stickyParams()),
          icon: 'fas fa-bookmark',
        },
        app.translator.trans('clarkwinkelmann-discussion-bookmarks.forum.page.link')
      )
    );
  });

  extend(IndexPage.prototype, 'setTitle', function () {
    if (app.current.get('routeName') === 'discussionBookmarks') {
      app.setTitle(app.translator.trans('clarkwinkelmann-discussion-bookmarks.forum.page.title'));
    }
  });

  // This code differs from the Subscription extension because it fixes https://github.com/flarum/core/issues/2516
  extend(GlobalSearchState.prototype, 'params', function (params) {
    params.bookmarked = app.current.get('routeName') === 'discussionBookmarks';
  });

  extend(DiscussionListState.prototype, 'requestParams', function (params) {
    if (this.params.bookmarked) {
      params.filter.bookmarked = true;
    }
  });
}
