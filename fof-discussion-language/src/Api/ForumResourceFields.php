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

use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\DiscussionLanguage\DiscussionLanguage;

/**
 * Expose the complete `DiscussionLanguage` list to clients by adding it as a
 * relationship to the /api endpoint.
 */
class ForumResourceFields
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Relationship\ToMany::make('discussionLanguages')
                ->type('discussion-languages')
                ->includable()
                ->get(fn () => $this->getLanguages()),
        ];
    }

    /**
     * @return DiscussionLanguage[]
     */
    protected function getLanguages(): array
    {
        $langs = DiscussionLanguage::query()->get();

        if ($this->settings->get('fof-discussion-language.showAnyLangOpt')) {
            $code = 'any';

            // Add a fake "any" language to the list
            $langs->prepend(new DiscussionLanguage(compact('code')));
        }

        return $langs->all();
    }
}
