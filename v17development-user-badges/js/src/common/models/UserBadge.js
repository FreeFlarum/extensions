import Model from 'flarum/common/Model';

export default class UserBadge extends Model {
  user() {
    return Model.hasOne('user').call(this);
  }
  badge() {
    return Model.hasOne('badge').call(this);
  }
  description() {
    return Model.attribute('description').call(this);
  }
  isPrimary() {
    return Model.attribute('isPrimary').call(this);
  }
  assignedAt() {
    return Model.attribute('assignedAt', Model.transformDate).call(this);
  }
  inUserCard() {
    return Model.attribute('inUserCard').call(this);
  }
}
