import Model from 'flarum/common/Model';

export default class BadgeCategory extends Model {
  name() {
    return Model.attribute('name').call(this);
  }
  order() {
    return Model.attribute('order').call(this);
  }
  description() {
    return Model.attribute('description').call(this);
  }
  isEnabled() {
    return Model.attribute('isEnabled').call(this);
  }
  createdAt() {
    return Model.attribute('createdAt', Model.transformDate).call(this);
  }
  isTable() {
    return Model.attribute('isTable').call(this);
  }
  badges() {
    return Model.hasMany('badges').call(this);
  }
}
