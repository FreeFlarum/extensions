<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Listener;

use Flarum\Tags\Event\Saving;
use Flarum\Tags\TagState;
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Support\Arr;

/**
 * This class is only called when `fof/follow-tags` is enabled.
 * The language is sent as request meta alongside the `subscription` attribute.
 */
class SaveLanguageOnSubscription
{
    public function handle(Saving $event): void
    {
        $language = Arr::get($event->data, 'meta.subscriptionLanguage');

        if (!$language || !is_string($language) || $event->actor->isGuest()) {
            return;
        }

        // Fetch the language ID using the provided language string
        $languageId = DiscussionLanguage::query()
                                        ->where('code', $language)
                                        ->value('id');

        // fof/follow-tags may have just created the state row, so it is queried again.
        $state = TagState::query()
            ->where('tag_id', $event->tag->id)
            ->where('user_id', $event->actor->id)
            ->first();

        if (!$state) {
            $state = new TagState();
            $state->tag_id = $event->tag->id;
            $state->user_id = $event->actor->id;
        }

        $state->dl_language_id = $languageId;
        $state->save();
    }
}
