<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Sticky;

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchCriteria;
use Flarum\Sticky\PinStickiedDiscussionsToTop as BasePinStickiedDiscussionsToTop;
use FoF\DiscussionLanguage\Search\LanguageFilter;

/**
 * Core sticky stops pinning as soon as any filter other than a single tag is active.
 * A language filter is conceptually still "all discussions" or the tag page in the
 * user's language, so sticky's logic runs as if the language filter wasn't there.
 */
class PinStickiedDiscussionsToTop extends BasePinStickiedDiscussionsToTop
{
    public function __invoke(DatabaseSearchState $state, SearchCriteria $criteria): void
    {
        $filters = $state->getActiveFilters();
        $otherFilters = array_filter($filters, fn ($filter) => !$filter instanceof LanguageFilter);

        if (count($otherFilters) !== count($filters)) {
            $languageless = new DatabaseSearchState($state->getActor(), $state->isFulltextSearch(), $state->getDefaultSort());
            $languageless->setQuery($state->getQuery());

            foreach ($otherFilters as $filter) {
                $languageless->addActiveFilter($filter);
            }

            $state = $languageless;
        }

        parent::__invoke($state, $criteria);
    }
}
