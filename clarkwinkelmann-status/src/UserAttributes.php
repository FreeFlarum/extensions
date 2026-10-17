<?php

namespace ClarkWinkelmann\Status;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Validation\Rule;

class UserAttributes
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function __invoke(): array
    {
        $canSee = fn (User $user, Context $context) => $context->getActor()->hasPermission('clarkwinkelmann-status.see');
        $canEdit = fn (User $user, Context $context) => $context->getActor()->can('clarkwinkelmannStatusEdit', $user);

        return [
            Schema\Str::make('clarkwinkelmannStatusEmoji')
                ->property('clarkwinkelmann_status_emoji')
                ->visible($canSee)
                ->writable($canEdit)
                ->nullable()
                ->rule(function () {
                    $emojiFileName = $this->settings->get('clarkwinkelmann-status.onlyCountries') ? 'flags' : 'all';

                    return Rule::in(json_decode(file_get_contents(__DIR__ . '/../resources/emojis/' . $emojiFileName . '.json')));
                }),
            Schema\Str::make('clarkwinkelmannStatusText')
                ->property('clarkwinkelmann_status_text')
                ->visible($canSee)
                ->writable($canEdit)
                ->nullable()
                ->maxLength(250)
                ->set(function (User $user, ?string $value) {
                    if ($this->settings->get('clarkwinkelmann-status.enableText')) {
                        $user->clarkwinkelmann_status_text = $value;
                    }
                }),
            Schema\Boolean::make('clarkwinkelmannStatusCanEdit')
                ->visible($canEdit)
                ->get(fn () => true),
        ];
    }
}
