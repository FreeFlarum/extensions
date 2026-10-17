<?php

namespace ClarkWinkelmann\AnonymousPosting\Event;

use Flarum\Discussion\Discussion;
use Flarum\User\User;

class DiscussionDeAnonymized
{
    public function __construct(public Discussion $discussion, public User $actor)
    {
    }
}
