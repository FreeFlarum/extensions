<?php

namespace ClarkWinkelmann\GroupInvitation\Events;

use ClarkWinkelmann\GroupInvitation\Invitation;
use Flarum\User\User;

class UsedInvitation
{
    public function __construct(public User $actor, public Invitation $invitation)
    {
    }
}
