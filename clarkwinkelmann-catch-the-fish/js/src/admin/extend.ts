import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import SettingsPage from './SettingsPage';

export default [
    ...commonExtend,

    new Extend.Admin()
        .page(SettingsPage)
        .permission(() => ({
            icon: 'fas fa-fish',
            label: app.translator.trans('clarkwinkelmann-catch-the-fish.admin.permissions.visible'),
            permission: 'catchthefish.visible',
            allowGuest: true,
        }), 'view')
        .permission(() => ({
            icon: 'fas fa-fish',
            label: app.translator.trans('clarkwinkelmann-catch-the-fish.admin.permissions.list-rankings'),
            permission: 'catchthefish.list-rankings',
            allowGuest: true,
        }), 'view')
        .permission(() => ({
            icon: 'fas fa-fish',
            label: app.translator.trans('clarkwinkelmann-catch-the-fish.admin.permissions.participate'),
            permission: 'catchthefish.participate',
        }), 'reply')
        .permission(() => ({
            icon: 'fas fa-fish',
            label: app.translator.trans('clarkwinkelmann-catch-the-fish.admin.permissions.choose-place'),
            permission: 'catchthefish.choose-place',
        }), 'reply')
        .permission(() => ({
            icon: 'fas fa-fish',
            label: app.translator.trans('clarkwinkelmann-catch-the-fish.admin.permissions.choose-name'),
            permission: 'catchthefish.choose-name',
        }), 'reply')
        .permission(() => ({
            icon: 'fas fa-fish',
            label: app.translator.trans('clarkwinkelmann-catch-the-fish.admin.permissions.moderate'),
            permission: 'catchthefish.moderate',
        }), 'moderate'),
];
