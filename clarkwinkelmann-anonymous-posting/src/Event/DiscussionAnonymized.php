<?php

namespace ClarkWinkelmann\AnonymousPosting\Event;

use Flarum\Discussion\Discussion;
use Flarum\User\User;

class DiscussionAnonymized
{
    public function __construct(public Discussion $discussion, public User $actor)
    {
    }
}
