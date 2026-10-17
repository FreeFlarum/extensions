<?php

namespace ClarkWinkelmann\GroupList;

use Flarum\Api\Context;
use Flarum\Api\Resource\ForumResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\Settings\SettingsRepositoryInterface;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/resources/less/forum.less')
        ->route('/groups', 'clarkwinkelmann-group-list'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/resources/less/admin.less'),
    new Extend\Locales(__DIR__ . '/resources/locale'),
    new Extend\ApiResource(GroupListItemResource::class),
    (new Extend\ApiResource(ForumResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('clarkwinkelmann-group-list.showSideNavLink')
                ->get(fn ($forum, Context $context) => resolve(SettingsRepositoryInterface::class)->get('clarkwinkelmann-group-list.showSideNavLink') !== '0' && $context->getActor()->hasPermission('clarkwinkelmann-group-list.see')),
            Schema\Boolean::make('clarkwinkelmann-group-list.showAvatarBadges')
                ->get(fn () => resolve(SettingsRepositoryInterface::class)->get('clarkwinkelmann-group-list.showAvatarBadges') === '1'),
            Schema\Boolean::make('clarkwinkelmann-group-list.showOnlineStatus')
                ->get(fn () => resolve(SettingsRepositoryInterface::class)->get('clarkwinkelmann-group-list.showOnlineStatus') === '1'),
        ]),
];
