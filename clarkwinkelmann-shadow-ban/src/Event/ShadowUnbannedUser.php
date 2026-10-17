<?php

namespace ClarkWinkelmann\ShadowBan\Event;

use Carbon\Carbon;
use Flarum\User\User;

/**
 * When a user is manually removed from shadow ban.
 * No event is dispatched when the ban naturally expires.
 *
 * @property User $user
 * @property User $actor
 * @property Carbon|null $previouslyBannedUntil
 */
class ShadowUnbannedUser
{
    public function __construct(public User $user, public User $actor, public ?Carbon $previouslyBannedUntil = null)
    {
    }
}
