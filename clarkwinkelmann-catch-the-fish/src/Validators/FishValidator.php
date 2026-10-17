<?php

namespace ClarkWinkelmann\CatchTheFish\Validators;

use Flarum\Foundation\AbstractValidator;

class FishValidator extends AbstractValidator
{
    protected array $rules = [
        'name' => 'required|string|min:3',
    ];
}
