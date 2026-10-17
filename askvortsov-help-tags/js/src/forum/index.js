import app from 'flarum/forum/app';
import Model from 'flarum/common/Model';
import Discussion from 'flarum/common/models/Discussion';
import addShowToAllBadge from './addShowToAllBadge';
import addShowToAllControl from './addShowToAllControl';

app.initializers.add('askvortsov-help-tags', () => {
  Discussion.prototype.showToAll = Model.attribute('showToAll');
  Discussion.prototype.canShowToAll = Model.attribute('canShowToAll');

  addShowToAllBadge();
  addShowToAllControl();
});
