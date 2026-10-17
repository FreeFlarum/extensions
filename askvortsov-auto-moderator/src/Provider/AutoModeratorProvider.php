<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Askvortsov\AutoModerator\Provider;

use Askvortsov\AutoModerator\CriteriaCalculator;
use Askvortsov\AutoModerator\Metric\MetricDriverInterface;
use Askvortsov\AutoModerator\Metric\MetricManager;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\User\Event\LoggedIn;
use Flarum\User\Event\Registered;
use Illuminate\Contracts\Events\Dispatcher;

class AutoModeratorProvider extends AbstractServiceProvider
{
    public function boot(Dispatcher $events, MetricManager $metrics)
    {
        $universalTriggers = [
            [
                'eventClass' => LoggedIn::class,
                'getUser' =>  function (LoggedIn $event) {
                    return $event->user;
                }
            ],
            [
                'eventClass' => Registered::class,
                'getUser' => function (Registered $event) {
                    return $event->user;
                }
            ]
        ];

        $triggers = collect($metrics->getDrivers())
            ->reduce(function (array $curr, MetricDriverInterface $driver) {
                $formatTriggers = collect($driver->eventTriggers())
                    ->map(function (callable $getUser, string $key) {
                        return [
                            'eventClass' => $key,
                            'getUser' => $getUser
                        ];
                    })
                    ->values()
                    ->toArray();
                return array_merge($curr, $formatTriggers);
            }, $universalTriggers);

        collect($triggers)
            ->groupBy('eventClass')
            ->each(function ($eventTriggers, string $eventClass) use ($events) {
                $events->listen($eventClass, function ($e) use ($eventTriggers, $eventClass) {
                    $users = $eventTriggers
                        ->map(function ($trigger) use ($e) {
                            return $trigger['getUser']($e);
                        })
                        ->filter()
                        ->unique('id');

                    foreach ($users as $actor) {
                        /** @var CriteriaCalculator */
                        $calculator = resolve(CriteriaCalculator::class);

                        $calculator->recalculate($actor, $eventClass);
                    }
                });
            });
    }
}
