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
use Flarum\Discussion\Discussion;
use Flarum\Locale\TranslatorInterface;
use Illuminate\Support\Arr;

class DiscussionResourceFields
{
    public function __construct(
        protected TranslatorInterface $translator
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('canChangeLanguage')
                ->get(fn (Discussion $discussion, Context $context) => $context->getActor()->can('changeLanguage', $discussion)),
            Schema\Relationship\ToOne::make('language')
                ->type('discussion-languages')
                ->includable()
                ->writable(fn (Discussion $discussion, Context $context) => $context->creating() || $context->getActor()->can('changeLanguage', $discussion))
                // Private discussions created by fof/byobu don't have a language.
                ->required(fn (Context $context) => $context->creating() && !$this->isPrivateDiscussion($context))
                ->validationMessages([
                    'language.required' => $this->translator->trans('fof-discussion-language.api.discussion.validation_error'),
                ]),
        ];
    }

    protected function isPrivateDiscussion(Context $context): bool
    {
        return Arr::get($context->body(), 'data.relationships.recipientUsers') || Arr::get($context->body(), 'data.relationships.recipientGroups');
    }
}
