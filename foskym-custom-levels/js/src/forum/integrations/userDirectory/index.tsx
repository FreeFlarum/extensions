import { extend } from 'flarum/common/extend';
import ForumApplication from 'flarum/forum/ForumApplication';

export default function userDirectry(app: ForumApplication) {
  extend('ext:fof/user-directory/common/utils/SortMap', 'sortMap', function (map) {
    map['most_exp'] = '-exp';
    map['least_exp'] = 'exp';
  });
}
