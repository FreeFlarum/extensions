<?php

namespace ClarkWinkelmann\ShadowBan\Event;

use Carbon\Carbon;
use Flarum\User\User;

/**
 * Dispatched when a user is manually shadow-banned.
 *
 * @property User $user
 * @property User $actor
 * @property Carbon|null $previouslyBannedUntil Previous ban end if the date was changed
 */
class ShadowBannedUser
{
    public function __construct(public User $user, public User $actor, public ?Carbon $previouslyBannedUntil = null)
    {
    }
}
