<?php

/*
 * This file is part of foskym/flarum-custom-levels.
 *
 * Copyright (c) 2024 FoskyM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoskyM\CustomLevels;

use Flarum\Api\Resource\UserResource;
use Flarum\Api\Sort\SortColumn;
use Flarum\Extend;
use Flarum\User\User;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/less/admin.less'),
    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\Settings())
        ->serializeToForum('foskym-custom-levels.levelText', 'foskym-custom-levels.levelText')
        ->serializeToForum('foskym-custom-levels.expText', 'foskym-custom-levels.expText')
        ->serializeToForum('foskym-custom-levels.expTooltip', 'foskym-custom-levels.expTooltip')
        ->serializeToForum('foskym-custom-levels.expFormula', 'foskym-custom-levels.expFormula')
        ->serializeToForum('foskym-custom-levels.levelFormula', 'foskym-custom-levels.levelFormula'),

    (new Extend\Console())
        ->command(Command\RefreshExpCommand::class)
        ->command(Command\RefreshQuestExpCommand::class)
        ->command(Command\DeleteExpLogsCommand::class),

    (new Extend\Model(User::class))
        ->cast('exp', 'int'),

    new Extend\ApiResource(Api\Resource\LevelResource::class),
    new Extend\ApiResource(Api\Resource\ExpLogResource::class),

    (new Extend\ApiResource(UserResource::class))
        ->fields(Api\UserResourceFields::class)
        ->sorts(fn () => [
            SortColumn::make('exp')
                ->ascendingAlias('least_exp')
                ->descendingAlias('most_exp'),
        ]),

    (new Extend\Routes('api'))
        ->get('/exp/refresh', 'custom-levels.exp.refresh', Api\Controller\RefreshExpController::class),

    (new Extend\Event())
        ->listen(Event\ExpUpdated::class, Listeners\ExpUpdatedListener::class),

    (new Extend\Notification())
        ->type(Notification\LevelUpdatedNotification::class, ['alert']),

    require(__DIR__ . '/src/Integration/Integrations.php'),
];
