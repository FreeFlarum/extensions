<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Search;

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use Flarum\Search\ValidateFilterTrait;
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Database\Eloquent\Builder;

/**
 * @implements FilterInterface<DatabaseSearchState>
 */
class LanguageFilter implements FilterInterface
{
    use ValidateFilterTrait;

    public function getFilterKey(): string
    {
        return 'language';
    }

    public function filter(SearchState $state, string|array $value, bool $negate): void
    {
        $codes = array_filter($this->asStringArray($value), fn ($c) => $c !== 'any');

        if (empty($codes)) {
            return;
        }

        $this->constrain($state->getQuery(), $negate, $codes);
    }

    protected function constrain(Builder $query, bool $negate, array $codes): void
    {
        $sub = DiscussionLanguage::whereIn('code', $codes)->select('id');

        if ($negate) {
            $query->where(function ($query) use ($sub) {
                $query->whereNotIn('discussions.language_id', $sub)
                    ->orWhereNull('discussions.language_id');
            });
        } else {
            $query->whereIn('discussions.language_id', $sub);
        }
    }
}
