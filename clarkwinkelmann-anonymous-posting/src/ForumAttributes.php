<?php

namespace ClarkWinkelmann\AnonymousPosting;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use Kilowhat\Formulaire\Submission;

class ForumAttributes
{
    public function __construct(protected SettingsRepositoryInterface $settings, protected AnonymityRepository $anonymityRepository)
    {
    }

    public function __invoke(): array
    {
        $canSwitch = fn ($forum, Context $context) => $context->getActor()->hasPermission('anonymous-posting.use');

        return [
            Schema\Boolean::make('defaultAnonymousPost')
                ->get(fn ($forum, Context $context) => $this->anonymityRepository->defaultValue($context->getActor())),
            Schema\Boolean::make('canAnonymousSwitch')
                ->visible($canSwitch)
                ->get(fn () => true),
            // We should also be able to return this value through Extend\Settings::serializeToForum, but it doesn't seem to work
            // might have been because of https://github.com/flarum/framework/issues/3438
            Schema\Str::make('anonymousHelpTextPosition')
                ->visible($canSwitch)
                ->get(fn () => $this->settings->get('anonymous-posting.composerHelpTextPosition') ?: 'visible'),
            Schema\Str::make('anonymousAvatarUrl')
                ->nullable()
                ->visible(function ($forum, Context $context) use ($canSwitch) {
                    return $canSwitch($forum, $context)
                        && is_array(json_decode((string)$this->settings->get('anonymous-posting.formulaireAvatars'), true))
                        && class_exists(Submission::class);
                })
                ->get(function ($forum, Context $context) {
                    return AnonymousAvatar::retrieve(
                        json_decode((string)$this->settings->get('anonymous-posting.formulaireAvatars'), true),
                        Submission::where('link_type', 'users')
                            ->where('link_id', $context->getActor()->id)
                            ->whereNull('hidden_at')
                            ->get()
                    );
                }),
        ];
    }
}
