<?php

namespace ClarkWinkelmann\CatchTheFish\Api;

use ClarkWinkelmann\CatchTheFish\Repositories\RoundRepository;
use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;

class ForumResourceFields
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected RoundRepository $rounds
    )
    {
    }

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('catchTheFishCanModerate')
                ->get(fn($forum, Context $context) => $context->getActor()->can('catchthefish.moderate')),
            Schema\Boolean::make('catchTheFishCanSeeRankingsPage')
                ->get(fn($forum, Context $context) => $context->getActor()->can('catchthefish.list-rankings')),
            Schema\Boolean::make('catchTheFishAlertRound')
                ->get(fn() => $this->settings->get('catch-the-fish.alertRound') !== '0'),
            Schema\Boolean::make('catchTheFishAnimateFlip')
                ->get(fn() => $this->settings->get('catch-the-fish.animateFlip') !== '0'),

            Schema\Relationship\ToMany::make('catchTheFishActiveRounds')
                ->type('catchthefish-rounds')
                ->includable()
                ->get(function ($forum, Context $context) {
                    if (!$context->getActor()->can('catchthefish.visible')) {
                        return [];
                    }

                    return $this->rounds->allActive()->all();
                }),
        ];
    }
}
