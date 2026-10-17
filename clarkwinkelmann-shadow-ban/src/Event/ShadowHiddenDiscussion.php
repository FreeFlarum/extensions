<?php

namespace ClarkWinkelmann\ShadowBan\Event;

use Flarum\Discussion\Discussion;
use Flarum\User\User;

/**
 * Dispatched when a discussion is shadow-hidden manually or automatically.
 *
 * @property Discussion $discussion
 * @property User|null $actor Actor if manually hidden, or null if hidden automatically because of a user ban
 */
class ShadowHiddenDiscussion
{
    public function __construct(public Discussion $discussion, public ?User $actor = null)
    {
    }
}
