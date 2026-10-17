<?php

namespace ClarkWinkelmann\DiscussionBookmarks;

use Flarum\Api\Resource;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Extend;
use Flarum\Search\Database\DatabaseSearchDriver;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/resources/less/forum.less')
        ->route('/bookmarked-discussions', 'discussion-bookmarks', Content\Bookmarks::class),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\ApiResource(Resource\DiscussionResource::class))
        ->fields(Api\DiscussionResourceFields::class),

    (new Extend\Settings())
        ->default('discussion-bookmarks.independentButton', '1')
        ->serializeToForum('independentDiscussionBookmarkButton', 'discussion-bookmarks.independentButton', 'boolval'),

    (new Extend\SearchDriver(DatabaseSearchDriver::class))
        ->addFilter(DiscussionSearcher::class, Filters\BookmarkedFilter::class),
];
