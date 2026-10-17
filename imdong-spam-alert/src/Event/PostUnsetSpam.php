<?php

namespace ImDong\SpamAlert\Event;


use Flarum\Post\Post;
use Flarum\User\User;

class PostUnsetSpam
{
    public function __construct(public Post $post, public User $user)
    {
    }
}
