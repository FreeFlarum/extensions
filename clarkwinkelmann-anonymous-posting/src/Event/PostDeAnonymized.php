<?php

namespace ClarkWinkelmann\AnonymousPosting\Event;

use Flarum\Post\Post;
use Flarum\User\User;

class PostDeAnonymized
{
    public function __construct(public Post $post, public User $actor)
    {
    }
}
