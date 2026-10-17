import Extend from 'flarum/common/extenders';
import Rule from '../common/models/rule';

export default [new Extend.Store().add('email_rules', Rule)];
