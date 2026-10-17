<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Tags\Tag;
use FoF\DiscussionLanguage\Api\Resource\DiscussionLanguageResource;
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * This class is only used when `fof/follow-tags` is enabled.
 */
class TagSubscriptionLanguageField
{
    public function __construct(
        protected Cache $cache
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Str::make('subscriptionLanguage')
                ->nullable()
                ->get(function (Tag $tag, Context $context) {
                    $state = $tag->stateFor($context->getActor());

                    if (!isset($state->dl_language_id)) {
                        return null;
                    }

                    $languages = $this->cache->rememberForever(DiscussionLanguageResource::CACHE_KEY, function () {
                        return DiscussionLanguage::all()->keyBy('id');
                    });

                    return $languages->get($state->dl_language_id)?->code;
                }),
        ];
    }
}
