<?php

namespace ClarkWinkelmann\DiscussionBookmarks\Filters;

use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use Illuminate\Database\Query\Builder;

class BookmarkedFilter implements FilterInterface
{
    public function getFilterKey(): string
    {
        return 'bookmarked';
    }

    public function filter(SearchState $state, array|string $value, bool $negate): void
    {
        $actor = $state->getActor();

        $method = $negate ? 'whereNotIn' : 'whereIn';
        $state->getQuery()->$method('discussions.id', function (Builder $query) use ($actor) {
            $query->select('discussion_id')
                ->from('discussion_user')
                ->where('user_id', $actor->id)
                ->whereNotNull('bookmarked_at');
        });
    }
}
