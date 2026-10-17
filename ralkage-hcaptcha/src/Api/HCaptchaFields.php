<?php

namespace Ralkage\HCaptcha\Api;

use Flarum\Api\Schema;

class HCaptchaFields
{
    public function __invoke(): array
    {
        return [
            // Write-only pass-through so the token reaches the Saving event listeners.
            Schema\Str::make('h-captcha-response')
                ->writableOnCreate()
                ->visible(false)
                ->nullable()
                ->set(fn () => null),
        ];
    }
}
