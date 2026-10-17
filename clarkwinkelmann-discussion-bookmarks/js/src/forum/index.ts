export { default as extend } from './extend';
import app from 'flarum/forum/app';
import addBadge from './addBadge';
import addDiscussionControls from './addDiscussionControls';
import addPage from './addPage';

app.initializers.add('clarkwinkelmann-discussion-bookmarks', () => {
  addBadge();
  addDiscussionControls();
  addPage();
});
