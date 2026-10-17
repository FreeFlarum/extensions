import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import AuthorizePage from './components/oauth/AuthorizePage';
import AuthorizedPage from './components/user/AuthorizedPage';

export default [
  ...commonExtend,

  new Extend.Routes() //
    .add('oauth.authorize', '/oauth/authorize', AuthorizePage)
    .add('user.authorized', '/u/:username/authorized', AuthorizedPage),
];
