<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Dislikes\Query;

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use Flarum\Search\ValidateFilterTrait;

/**
 * @implements FilterInterface<DatabaseSearchState>
 */
class DislikedFilter implements FilterInterface
{
    use ValidateFilterTrait;

    public function getFilterKey(): string
    {
        return 'disliked';
    }

    public function filter(SearchState $state, string|array $value, bool $negate): void
    {
        $dislikedId = trim($this->asString($value), '"');

        $state
            ->getQuery()
            ->whereIn('id', function ($query) use ($dislikedId) {
                $query->select('user_id')
                    ->from('post_dislikes')
                    ->where('post_id', $dislikedId);
            }, 'and', $negate);
    }
}
