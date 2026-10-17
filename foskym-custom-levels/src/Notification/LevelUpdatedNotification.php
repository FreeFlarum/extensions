<?php

/*
 * This file is part of foskym/flarum-custom-levels.
 *
 * Copyright (c) 2024 FoskyM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
namespace FoskyM\CustomLevels\Notification;

use Flarum\Notification\AlertableInterface;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\User\User;
use FoskyM\CustomLevels\Model\Level;
use FoskyM\CustomLevels\Model\ExpLog;

class LevelUpdatedNotification implements BlueprintInterface, AlertableInterface
{
    public $user;

    public function __construct(User $actor, public Level $level)
    {
        $this->user = $actor;
    }

    public function getSubject(): ?\Flarum\Database\AbstractModel
    {
        return $this->level;
    }

    public function getFromUser(): ?\Flarum\User\User
    {
        return $this->user;
    }

    public function getData(): mixed
    {
        return [
            'level' => $this->level->name,
        ];
    }

    public static function getType(): string
    {
        return 'levelUpdated';
    }

    public static function getSubjectModel(): string
    {
        return Level::class;
    }
}