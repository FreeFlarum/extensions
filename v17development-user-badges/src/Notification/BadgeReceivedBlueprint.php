<?php

namespace V17Development\FlarumUserBadges\Notification;

use Flarum\Database\AbstractModel;
use Flarum\Notification\AlertableInterface;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\User\User;
use V17Development\FlarumUserBadges\UserBadge\UserBadge;

class BadgeReceivedBlueprint implements BlueprintInterface, AlertableInterface
{
    public function __construct(
        public UserBadge $badge
    ) {
    }

    public function getFromUser(): ?User
    {
        return null;
    }

    public function getSubject(): ?AbstractModel
    {
        return $this->badge;
    }

    public function getData(): mixed
    {
        return null;
    }

    public static function getType(): string
    {
        return 'badgeReceived';
    }

    public static function getSubjectModel(): string
    {
        return UserBadge::class;
    }
}
