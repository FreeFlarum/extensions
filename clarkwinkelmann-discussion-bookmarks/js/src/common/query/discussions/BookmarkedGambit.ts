import app from 'flarum/common/app';
import { BooleanGambit } from 'flarum/common/query/IGambit';

export default class BookmarkedGambit extends BooleanGambit {
  key(): string {
    return app.translator.trans('clarkwinkelmann-discussion-bookmarks.lib.gambits.bookmarked.key', {}, true);
  }

  filterKey(): string {
    return 'bookmarked';
  }

  enabled(): boolean {
    return !!app.session.user;
  }
}
