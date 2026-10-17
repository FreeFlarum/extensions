<?php

namespace ClarkWinkelmann\ShadowBan;

use Carbon\Carbon;
use ClarkWinkelmann\ShadowBan\Event\ShadowBannedUser;
use ClarkWinkelmann\ShadowBan\Event\ShadowUnbannedUser;
use DateTimeInterface;
use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\User\User;

class UserAttributes
{
    public function __invoke(): array
    {
        $canShadowBan = fn (User $user, Context $context) => $context->getActor()->can('shadowBan', $user);

        return [
            Schema\Attribute::make('shadowBannedUntil')
                ->visible($canShadowBan)
                ->writable($canShadowBan)
                ->nullable()
                // Prevent choosing a date that exceeds the TIMESTAMP column max value
                ->rule('date')
                ->rule('before:2038-01-19 03:14:07 UTC')
                ->get(fn (User $user) => $user->shadow_banned_until?->format(DateTimeInterface::RFC3339))
                ->set(function (User $user, $shadowBannedUntil, Context $context) {
                    if ($shadowBannedUntil) {
                        $user->raise(new ShadowBannedUser($user, $context->getActor(), $user->shadow_banned_until));

                        $user->shadow_banned_until = Carbon::parse($shadowBannedUntil);
                    } else {
                        $user->raise(new ShadowUnbannedUser($user, $context->getActor(), $user->shadow_banned_until));

                        $user->shadow_banned_until = null;
                    }
                }),
            Schema\Boolean::make('canShadowBan')
                ->visible($canShadowBan)
                ->get(fn () => true),
        ];
    }
}
