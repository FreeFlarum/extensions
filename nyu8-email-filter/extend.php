<?php

use Flarum\Extend;
use Flarum\User\Event\Saving;
use NYU8\FlarumEmailFilter\Event\UserSavingListener;
use NYU8\FlarumEmailFilter\RuleResource;

return [
  (new Extend\Frontend('forum'))
    ->js(__DIR__ . '/js/dist/forum.js'),
  (new Extend\Frontend('admin'))
    ->js(__DIR__ . '/js/dist/admin.js')
    ->css(__DIR__ . '/less/admin.less'),
  (new Extend\Locales(__DIR__ . '/locale')),
  new Extend\ApiResource(RuleResource::class),
  (new Extend\Event)
    ->listen(Saving::class, UserSavingListener::class),
];
