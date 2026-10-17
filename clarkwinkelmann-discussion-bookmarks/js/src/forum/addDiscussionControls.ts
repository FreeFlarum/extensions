import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import DiscussionPage from 'flarum/forum/components/DiscussionPage';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import Discussion from 'flarum/common/models/Discussion';

function bookmarkButton(discussion: Discussion, translation: string, className: string = '') {
  const bookmarked = discussion.attribute('bookmarked');

  return Button.component(
    {
      className: className + (bookmarked ? ' Button--bookmarked' : ''),
      icon: bookmarked ? 'fas fa-bookmark' : 'far fa-bookmark',
      onclick() {
        discussion
          .save({
            bookmarked: !bookmarked,
          })
          .then(() => {
            m.redraw();
          });
      },
    },
    app.translator.trans('clarkwinkelmann-discussion-bookmarks.forum.' + translation + '.' + (bookmarked ? 'remove' : 'add'))
  );
}

export default function () {
  extend(DiscussionControls, 'userControls', (items, discussion, context) => {
    if (!app.session.user || (app.forum.attribute('independentDiscussionBookmarkButton') && context instanceof DiscussionPage)) {
      return;
    }

    items.add('bookmark', bookmarkButton(discussion, 'dropdownButton'));
  });

  extend(DiscussionPage.prototype, 'sidebarItems', function (items) {
    if (!app.session.user || !app.forum.attribute('independentDiscussionBookmarkButton') || !this.discussion) {
      return;
    }

    items.add('bookmark', bookmarkButton(this.discussion, 'independentButton', 'Button Button--bookmark'));
  });

  // Blog pages don't re-use Flarum's discussion sidebarItems, so custom code is needed to have a bookmark button on that page
  extend('ext:fof/blog/forum/pages/BlogItem', 'contentItems', function (items) {
    if (!app.session.user || !this.article) {
      return;
    }

    items.add(
      'bookmark',
      m('.FlarumBlog-Article-Content-Bookmark-Button', bookmarkButton(this.article, 'independentButton', 'Button Button--bookmark')),
      78 // Just below the edit controls that admins can see
    );
  });
}
