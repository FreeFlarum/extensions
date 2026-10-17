<?php

namespace ClarkWinkelmann\ShadowBan\Event;

use Flarum\Post\Post;
use Flarum\User\User;

/**
 * When a post is manually restored from shadow-hide.
 *
 * @property Post $post
 * @property User $actor
 */
class ShadowRestoredPost
{
    public function __construct(public Post $post, public User $actor)
    {
    }
}
