<?php

namespace ClarkWinkelmann\MoneyRewards;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;

class ForumAttributes
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function __invoke(): array
    {
        $customAmounts = fn (object $forum, Context $context) => $context->getActor()->hasPermission('money-rewards.customAmounts');

        return [
            Schema\Arr::make('moneyRewardsPreselection')
                ->get(function () {
                    $preselection = $this->settings->get('money-rewards.preselection');

                    return $preselection ? explode(',', $preselection) : [];
                }),
            Schema\Boolean::make('moneyRewardsCustomAmounts')
                ->visible($customAmounts)
                ->get(fn () => true),
            Schema\Integer::make('moneyRewardsCustomAmountsMin')
                ->visible($customAmounts)
                ->get(fn () => max(0, (int)$this->settings->get('money-rewards.min'))),
            Schema\Integer::make('moneyRewardsCustomAmountsMax')
                ->visible($customAmounts)
                ->nullable()
                ->get(fn () => ((int)$this->settings->get('money-rewards.max')) ?: null),
            Schema\Integer::make('moneyRewardsCustomAmountsDecimals')
                ->visible($customAmounts)
                ->get(fn () => max(0, (int)$this->settings->get('money-rewards.decimals'))),
            Schema\Boolean::make('moneyRewardsCreateMoney')
                ->visible(fn (object $forum, Context $context) => $context->getActor()->hasPermission('money-rewards.createMoney'))
                ->get(fn () => true),
        ];
    }
}
