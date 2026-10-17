<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Extend;
use Flarum\Search\Database\DatabaseSearchDriver;
use Flarum\Tags\Api\Resource\TagResource;
use Flarum\Tags\Event\Creating as TagCreating;
use Flarum\Tags\Event\Saving as TagSaving;
use Flarum\Tags\Tag;
use Flarum\Tags\TagState;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    new Extend\ApiResource(Api\Resource\DiscussionLanguageResource::class),

    (new Extend\ServiceProvider())
        ->register(Provider\LanguageFilterProvider::class),

    (new Extend\Middleware('forum'))
        ->add(Middleware\AddLanguageFilter::class),

    (new Extend\Model(Discussion::class))
        ->cast('language_id', 'int')
        ->belongsTo('language', DiscussionLanguage::class, 'language_id'),

    (new Extend\Model(Tag::class))
        ->cast('localised_last_discussion', 'string'),

    (new Extend\Model(TagState::class))
        ->cast('dl_language_id', 'int'),

    (new Extend\Event())
        ->listen(TagCreating::class, Listener\TagCreating::class)
        ->subscribe(Listener\UpdateTagMetadata::class),

    (new Extend\SearchDriver(DatabaseSearchDriver::class))
        ->addFilter(DiscussionSearcher::class, Search\LanguageFilter::class),

    (new Extend\Policy())
        ->modelPolicy(Discussion::class, Access\DiscussionPolicy::class),

    (new Extend\ApiResource(Resource\ForumResource::class))
        ->fields(Api\ForumResourceFields::class)
        ->endpoint(Endpoint\Show::class, function (Endpoint\Show $endpoint) {
            return $endpoint->addDefaultInclude(['discussionLanguages']);
        }),

    (new Extend\ApiResource(Resource\DiscussionResource::class))
        ->fields(Api\DiscussionResourceFields::class)
        ->endpoint([Endpoint\Index::class, Endpoint\Show::class, Endpoint\Create::class], function (Endpoint\Index|Endpoint\Show|Endpoint\Create $endpoint) {
            return $endpoint->addDefaultInclude(['language']);
        }),

    (new Extend\Settings())
        ->default('fof-discussion-language.showFlags', true)
        ->default('fof-discussion-language.showAnyLangOpt', true)
        ->default('fof-discussion-language.allow_language_change', '10')
        ->default('fof-discussion-language.native', false)
        ->default('fof-discussion-language.filter_language_on_http_request', false)
        ->default('fof-discussion-language.useLocaleForTagsPageLastDiscussion', false)
        ->default('fof-discussion-language.composerLocaleDefault', false)
        ->serializeToForum('fof-discussion-language.composerLocaleDefault', 'fof-discussion-language.composerLocaleDefault', 'boolVal')
        ->serializeToForum('fof-discussion-language.showAnyLangOpt', 'fof-discussion-language.showAnyLangOpt', 'boolVal')
        ->serializeToForum('fof-discussion-language.useLocaleForTagsPageLastDiscussion', 'fof-discussion-language.useLocaleForTagsPageLastDiscussion', 'boolVal'),

    (new Extend\ApiResource(TagResource::class))
        ->fields(Api\TagResourceFields::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('fof-follow-tags', function () {
            return [
                (new Extend\ApiResource(TagResource::class))
                    ->fields(Api\TagSubscriptionLanguageField::class),

                (new Extend\Event())
                    ->listen(TagSaving::class, Listener\SaveLanguageOnSubscription::class),

                (new Extend\Notification())
                    ->beforeSending(CheckNotificationRecipients::class),
            ];
        }),
];
