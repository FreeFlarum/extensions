<?php

namespace Justoverclock\Feedback\Api;

use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;

class FeedbackSettings
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function __invoke(): array
    {
        return [
            // Only show the feedback widget when there is a destination to send feedback to
            Schema\Arr::make('feedback')
                ->visible(fn () => ! empty($this->settings->get('justoverclock-feedback.ContactMail')))
                ->get(fn () => [
                    'email' => (bool) $this->settings->get('justoverclock-feedback.collect-email', false),
                    'position' => (string) $this->settings->get('justoverclock-feedback.position', 'right'),
                    'backgroundColor' => (string) $this->settings->get('justoverclock-feedback.backgroundColor', '#fff'),
                    'fontColor' => (string) $this->settings->get('justoverclock-feedback.fontColor', '#000'),
                ]),
        ];
    }
}
