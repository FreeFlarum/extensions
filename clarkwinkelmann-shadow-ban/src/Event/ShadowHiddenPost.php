<?php

namespace ClarkWinkelmann\ShadowBan\Event;

use Flarum\Post\Post;
use Flarum\User\User;

/**
 * Dispatched when a post is shadow-hidden manually or automatically.
 *
 * @property Post $post
 * @property User|null $actor Actor if manually hidden, or null if hidden automatically because of a user ban
 */
class ShadowHiddenPost
{
    public function __construct(public Post $post, public ?User $actor = null)
    {
    }
}
