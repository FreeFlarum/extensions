<?php

namespace V17Development\FlarumUserBadges\UserBadge\Search;

use Flarum\Search\Database\AbstractSearcher;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder;
use V17Development\FlarumUserBadges\UserBadge\UserBadge;

class UserBadgeSearcher extends AbstractSearcher
{
    public function getQuery(User $actor): Builder
    {
        return UserBadge::query();
    }
}
