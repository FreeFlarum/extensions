<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Provider;

use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Sticky\PinStickiedDiscussionsToTop;
use FoF\DiscussionLanguage\Sticky\PinStickiedDiscussionsToTop as CustomPinStickiedDiscussionsToTop;

class LanguageFilterProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->extend('flarum.search.mutators', function ($mutators) {
            if (isset($mutators[DiscussionSearcher::class]) && is_array($mutators[DiscussionSearcher::class])) {
                foreach ($mutators[DiscussionSearcher::class] as $key => $mutator) {
                    if ($mutator === PinStickiedDiscussionsToTop::class) {
                        $mutators[DiscussionSearcher::class][$key] = CustomPinStickiedDiscussionsToTop::class;
                    }
                }
            }

            return $mutators;
        });
    }
}
