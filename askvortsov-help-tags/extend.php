<?php

/*
 * This file is part of askvortsov/flarum-help-tags
 *
 *  Copyright (c) 2021 Alexander Skvortsov.
 *
 *  For detailed copyright and license information, please view the
 *  LICENSE file that was distributed with this source code.
 */

namespace Askvortsov\FlarumHelpTags;

use Flarum\Api\Context;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;
use Flarum\Extend;
use Flarum\Tags\Tag;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Policy())
        ->globalPolicy(Access\GlobalPolicy::class),

    (new Extend\ModelVisibility(Discussion::class))
        ->scope(Access\ScopeDiscussionVisibility::class, 'viewForumInRestrictedTags'),

    (new Extend\ModelVisibility(Tag::class))
        ->scope(Access\ScopeTagVisibility::class),

    (new Extend\ApiResource(Resource\DiscussionResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('showToAll')
                ->get(fn (Discussion $discussion) => (bool) $discussion->show_to_all)
                ->writable(fn (Discussion $discussion, Context $context) => $context->getActor()->isAdmin())
                ->set(function (Discussion $discussion, bool $showToAll) {
                    $discussion->show_to_all = $showToAll;
                }),
            Schema\Boolean::make('canShowToAll')
                ->get(fn (Discussion $discussion, Context $context) => $context->getActor()->isAdmin()),
        ]),

    new Extend\Locales(__DIR__.'/resources/locale'),
];
