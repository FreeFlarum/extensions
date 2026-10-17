<?php

namespace ClarkWinkelmann\AnonymousPosting\Filter;

use Flarum\Http\SlugManager;
use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use Flarum\Search\ValidateFilterTrait;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Same as Flarum's Post\Filter\AuthorFilter / Discussion\Search\Filter\AuthorFilter but includes resources created anonymously.
 * Replaces the originals through the SearchDriver extender.
 *
 * @implements FilterInterface<DatabaseSearchState>
 */
abstract class AbstractAuthorFilter implements FilterInterface
{
    use ValidateFilterTrait;

    protected static string $table = '';

    public function __construct(protected SlugManager $slugManager)
    {
    }

    public function getFilterKey(): string
    {
        return 'author';
    }

    public function filter(SearchState $state, array|string $value, bool $negate): void
    {
        $actor = $state->getActor();
        $slugDriver = $this->slugManager->forResource(User::class);
        $ids = [];

        foreach ($this->asStringArray($value) as $slug) {
            try {
                $ids[] = $slugDriver->fromSlug($slug, $actor)->id;
            } catch (ModelNotFoundException) {
                // Slug does not match any user; skip it.
            }
        }

        // Only allow seeing own anonymous when filtering by a single value, otherwise the SQL would get too complex
        $canSeeAnonymous = (count($ids) === 1 && $ids[0] === $actor->id && !$actor->isGuest()) || $actor->hasPermission('anonymous-posting.reveal');

        $state->getQuery()->where(function (Builder $where) use ($ids, $negate, $canSeeAnonymous) {
            $where->whereIn(static::$table . '.user_id', $ids, 'and', $negate);

            if ($canSeeAnonymous) {
                $where->whereIn(static::$table . '.anonymous_user_id', $ids, $negate ? 'and' : 'or', $negate);
            }
        });
    }
}
