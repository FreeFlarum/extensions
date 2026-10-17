import Extend from 'flarum/common/extenders';
import IndexPage from 'flarum/forum/components/IndexPage';
import commonExtend from '../common/extend';

export default [...commonExtend, new Extend.Routes().add('discussionBookmarks', '/bookmarked-discussions', IndexPage)];
