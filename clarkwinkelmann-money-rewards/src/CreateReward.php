<?php

namespace ClarkWinkelmann\MoneyRewards;

use AntoineFr\Money\Event\MoneyUpdated;
use Flarum\Api\Context;
use Flarum\Foundation\ValidationException;
use Flarum\Locale\TranslatorInterface;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;

class CreateReward
{
    public function __construct(protected SettingsRepositoryInterface $settings, protected Dispatcher $events, protected Factory $validation, protected TranslatorInterface $translator, protected LoggerInterface $log)
    {
    }

    public function __invoke(Context $context): Post
    {
        $attributes = (array)Arr::get($context->body(), 'data.attributes');

        $actor = $context->getActor();

        /** @var Post $post */
        $post = $context->model;

        $amount = floatval(Arr::get($attributes, 'amount'));
        $comment = (string)Arr::get($attributes, 'comment');

        $this->validation->make([
            'comment' => $comment,
        ], [
            'comment' => 'nullable|string|max:20000',
        ])->validate();

        $this->validateAmount($amount, $actor);

        $actorMoneyUpdated = false;
        $recipientMoneyUpdated = false;
        $recipient = null;
        $reward = null;

        $createMoney = (bool)Arr::get($attributes, 'createMoney');

        if ($createMoney) {
            $actor->assertCan('money-rewards.createMoney');
        } else {
            if ($actor->money < $amount) {
                throw new ValidationException([
                    'amount' => $this->translator->trans('clarkwinkelmann-money-rewards.api.error.notEnoughFunds'),
                ]);
            }
        }

        try {
            if (!$createMoney) {
                $actor->money -= $amount;
                $actor->save();
                $actorMoneyUpdated = true;
            }

            $recipient = $post->user;

            $recipient->money += $amount;
            $recipient->save();
            $recipientMoneyUpdated = true;

            $reward = new Reward();
            $reward->post()->associate($post);
            $reward->giver()->associate($actor);
            $reward->receiver()->associate($recipient);
            $reward->amount = $amount;
            $reward->new_money = $createMoney;
            $reward->comment = $comment;
            $reward->save();
        } catch (\Exception $exception) {
            $this->log->error(
                '[money-rewards] An error occurred while processing the reward. Some of the money might not have been attributed' . PHP_EOL .
                ' | Amount: ' . $amount . PHP_EOL .
                ' | Create money: ' . ($createMoney ? 'yes' : 'no') . PHP_EOL .
                ' | Actor (#' . $actor->id . ') money updated: ' . ($actorMoneyUpdated ? 'yes' : 'no') . PHP_EOL .
                ' | Recipient (' . ($recipient ? '#' . $recipient->id : 'N/A') . ') money updated: ' . ($recipientMoneyUpdated ? 'yes' : 'no') . PHP_EOL .
                ' | History entry: ' . ($reward ? 'created, #' . $reward->id : 'not created'),
            );

            throw $exception;
        }

        if ($actorMoneyUpdated) {
            $this->events->dispatch(new MoneyUpdated($actor));
        }
        $this->events->dispatch(new MoneyUpdated($recipient));

        return $post;
    }

    protected function validateAmount(float $amount, User $actor): void
    {
        $preselection = $this->settings->get('money-rewards.preselection');
        $preselectionException = null;

        if ($preselection) {
            try {
                $this->validation->make([
                    'amount' => $amount,
                ], [
                    'amount' => 'required|numeric|in:' . $preselection,
                ])->validate();

                // No need to validate custom amounts if it matched one of the preselections
                return;
            } catch (\Exception $exception) {
                $preselectionException = $exception;
            }
        }

        if ($actor->hasPermission('money-rewards.customAmounts')) {
            $validationRules = [
                'required',
                'numeric',
                'min:' . (max(0, (int)$this->settings->get('money-rewards.min'))),
                function ($attribute, $value, $fail) {
                    $maxDecimals = max(0, (int)$this->settings->get('money-rewards.decimals'));
                    // https://stackoverflow.com/a/60638478
                    $decimalsCount = (int)strpos(strrev((string)$value), '.');

                    if ($decimalsCount > $maxDecimals) {
                        $fail($this->translator->trans('clarkwinkelmann-money-rewards.api.error.decimals', [
                            '{decimals}' => $maxDecimals,
                        ]));
                    }
                },
            ];

            if ($max = $this->settings->get('money-rewards.max')) {
                $validationRules[] = "max:$max";
            }

            $this->validation->make([
                'amount' => $amount,
            ], [
                'amount' => $validationRules,
            ])->validate();

            return;
        }

        // Only throw preselection exception if custom amounts were not processed
        if ($preselectionException) {
            throw $preselectionException;
        }
    }
}
