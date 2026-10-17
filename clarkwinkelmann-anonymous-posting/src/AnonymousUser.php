<?php

namespace ClarkWinkelmann\AnonymousPosting;

use Flarum\User\User;

/**
 * To be used as a workaround for code that can't handle null ->user relationships,
 * like the subject and blade templates of both mentions notifications
 * It doesn't extend Guest because the ID needs to be NULL, it ends up in notifications.from_user_id column via getFromUser()
 */
class AnonymousUser extends User
{
    public function getDisplayNameAttribute(): string
    {
        return resolve('translator')->trans('clarkwinkelmann-anonymous-posting.lib.userMeta.username');
    }

    public function isGuest(): bool
    {
        return true;
    }

    protected function newRelatedInstance($class)
    {
        // Trying to call relationships on the guest/anonymous user results in SQL errors with table names auto-generated from class names and relations
        throw new \Exception('[anonymous-posting] Eloquent relationship called on AnonymousUser instance. This is not supported. You probably have an incompatible extension.');
    }
}
