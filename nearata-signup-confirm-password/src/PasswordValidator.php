<?php

namespace Nearata\SignUpConfirmPassword;

class PasswordValidator extends \Flarum\User\UserValidator
{
    protected function getRules(): array
    {
        $rules = parent::getRules();

        $rules['password'][] = 'confirmed';

        return $rules;
    }
}
