import Model from 'flarum/common/Model';

export default class Badge extends Model {
  name() {
    return Model.attribute('name').call(this);
  }
  icon() {
    return Model.attribute('icon').call(this);
  }
  order() {
    return Model.attribute('order').call(this);
  }
  image() {
    return Model.attribute('image').call(this);
  }
  description() {
    return Model.attribute('description').call(this);
  }
  isVisible() {
    return Model.attribute('isVisible').call(this);
  }
  createdAt() {
    return Model.attribute('createdAt', Model.transformDate).call(this);
  }
  earnedAmount() {
    return Model.attribute('earnedAmount').call(this);
  }
  category() {
    return Model.hasOne('category').call(this);
  }
  backgroundColor() {
    return Model.attribute('backgroundColor').call(this);
  }
  iconColor() {
    return Model.attribute('iconColor').call(this);
  }
  labelColor() {
    return Model.attribute('labelColor').call(this);
  }
}
