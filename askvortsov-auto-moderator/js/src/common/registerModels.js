import Model from 'flarum/common/Model';
import User from 'flarum/common/models/User';
import Criterion from './models/Criterion';

export default function registerModels() {
  app.store.models.criteria = Criterion;

  User.prototype.criteria = Model.hasMany('criteria');
}
