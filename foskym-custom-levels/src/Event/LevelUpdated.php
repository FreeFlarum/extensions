<?php

/*
 * This file is part of foskym/flarum-custom-levels.
 *
 * Copyright (c) 2024 FoskyM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
namespace FoskyM\CustomLevels\Event;

use Flarum\User\User;
use FoskyM\CustomLevels\Model\Level;

class LevelUpdated
{
    public function __construct(public ?User $user = null, public ?Level $recent_level = null, public ?Level $current_level = null)
    {
    }
}