import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';

export default [
  new Extend.Admin()
    .setting(() => ({
      setting: 'serakoi-flarumstaffbadge.staffBadge',
      name: 'staffBadge',
      type: 'text',
      label: app.translator.trans('serakoi-flarumstaffbadge.admin.staffBadge.label'),
      help: app.translator.trans('serakoi-flarumstaffbadge.admin.staffBadge.help'),
    }))
    .setting(() => ({
      setting: 'serakoi-flarumstaffbadge.staffBadgeTextColor',
      name: 'staffBadgeTextColor',
      type: 'text',
      label: app.translator.trans('serakoi-flarumstaffbadge.admin.staffBadgeTextColor.label'),
      help: app.translator.trans('serakoi-flarumstaffbadge.admin.staffBadgeTextColor.help'),
    }))
    .setting(() => ({
      setting: 'serakoi-flarumstaffbadge.staffBadgeTextBg',
      name: 'staffBadgeTextBg',
      type: 'text',
      label: app.translator.trans('serakoi-flarumstaffbadge.admin.staffBadgeTextBg.label'),
      help: app.translator.trans('serakoi-flarumstaffbadge.admin.staffBadgeTextBg.help'),
    })),
];
