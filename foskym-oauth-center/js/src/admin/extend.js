import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import SettingsPage from './components/SettingsPage';

export default [
  ...commonExtend,

  new Extend.Admin().page(SettingsPage).permission(
    () => ({
      icon: 'fas fa-user-friends',
      label: app.translator.trans('foskym-oauth-center.admin.permission.use_oauth'),
      permission: 'foskym-oauth-center.use-oauth',
    }),
    'view',
    95
  ),
];
