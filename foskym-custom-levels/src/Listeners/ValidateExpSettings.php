<?php

/*
 * This file is part of foskym/flarum-custom-levels.
 *
 * Copyright (c) 2024 FoskyM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoskyM\CustomLevels\Listeners;

use Flarum\Foundation\ValidationException;
use Flarum\Locale\TranslatorInterface;
use Flarum\Settings\Event\Saving;
use FoskyM\CustomLevels\ExpValue;

class ValidateExpSettings
{
    public function __construct(protected TranslatorInterface $translator)
    {
    }

    public function handle(Saving $event): void
    {
        $errors = [];

        foreach (ExpValue::SETTINGS as $key) {
            if (array_key_exists($key, $event->settings) && ExpValue::parse($event->settings[$key]) === null) {
                $errors[$key] = $this->translator->trans('foskym-custom-levels.admin.settings.invalid_exp_value', [
                    'field' => $this->translator->trans('foskym-custom-levels.admin.settings.'.substr($key, strlen('foskym-custom-levels.'))),
                    'max' => ExpValue::MAX,
                ]);
            }
        }

        if ($errors) {
            throw new ValidationException($errors);
        }
    }
}
