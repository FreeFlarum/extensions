<?php

namespace Nearata\EmbedVideo\Api\Resource;

use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;

class ForumResourceFields
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('embedVideoDash')
                ->get(fn () => (bool) $this->settings->get('nearata-embed-video.admin.settings.video_type.dash')),
            Schema\Boolean::make('embedVideoFlv')
                ->get(fn () => (bool) $this->settings->get('nearata-embed-video.admin.settings.video_type.flv')),
            Schema\Boolean::make('embedVideoHls')
                ->get(fn () => (bool) $this->settings->get('nearata-embed-video.admin.settings.video_type.hls')),
            Schema\Boolean::make('embedVideoShaka')
                ->get(fn () => (bool) $this->settings->get('nearata-embed-video.admin.settings.video_type.shaka')),
            Schema\Boolean::make('embedVideoWebTorrent')
                ->get(fn () => (bool) $this->settings->get('nearata-embed-video.admin.settings.video_type.webtorrent')),
            Schema\Boolean::make('embedVideoAirplay')
                ->get(fn () => (bool) $this->settings->get('nearata-embed-video.admin.settings.options.airplay')),
            Schema\Boolean::make('embedVideoHotkey')
                ->get(fn () => (bool) $this->settings->get('nearata-embed-video.admin.settings.options.hotkey')),
            Schema\Boolean::make('embedVideoQualitySwitching')
                ->get(fn () => (bool) $this->settings->get('nearata-embed-video.admin.settings.options.quality_switching')),
            Schema\Str::make('embedVideoTheme')
                ->get(fn () => (string) $this->settings->get('nearata-embed-video.admin.settings.options.theme')),
            Schema\Str::make('embedVideoLogo')
                ->get(fn () => (string) $this->settings->get('nearata-embed-video.admin.settings.options.logo')),
            Schema\Str::make('embedVideoLang')
                ->get(fn () => (string) $this->settings->get('nearata-embed-video.admin.settings.options.lang')),
            Schema\Boolean::make('embedVideoModal')
                ->get(fn () => (bool) $this->settings->get('nearata-embed-video.admin.settings.options.modal_enabled')),
        ];
    }
}
