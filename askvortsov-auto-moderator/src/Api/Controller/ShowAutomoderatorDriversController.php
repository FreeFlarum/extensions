<?php

/*
 * This file is part of askvortsov/flarum-auto-moderator
 *
 *  Copyright (c) 2021 Alexander Skvortsov.
 *
 *  For detailed copyright and license information, please view the
 *  LICENSE file that was distributed with this source code.
 */

namespace Askvortsov\AutoModerator\Api\Controller;

use Askvortsov\AutoModerator\Action\ActionDriverInterface;
use Askvortsov\AutoModerator\Action\ActionManager;
use Askvortsov\AutoModerator\Metric\MetricDriverInterface;
use Askvortsov\AutoModerator\Metric\MetricManager;
use Askvortsov\AutoModerator\Requirement\RequirementDriverInterface;
use Askvortsov\AutoModerator\Requirement\RequirementManager;
use Flarum\Http\RequestUtil;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShowAutomoderatorDriversController implements RequestHandlerInterface
{
    public function __construct(protected ActionManager $actions, protected MetricManager $metrics, protected RequirementManager $requirements)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        RequestUtil::getActor($request)->assertAdmin();

        $action = array_merge(
            $this->serializeActionDrivers($this->actions->getDrivers()),
            $this->serializeActionDrivers($this->actions->getDrivers(true), true)
        );

        $metric = array_merge(
            $this->serializeMetricDrivers($this->metrics->getDrivers()),
            $this->serializeMetricDrivers($this->metrics->getDrivers(true), true)
        );

        $requirement = array_merge(
            $this->serializeRequirementDrivers($this->requirements->getDrivers()),
            $this->serializeRequirementDrivers($this->requirements->getDrivers(true), true)
        );

        return new JsonResponse([
            'data' => [
                'type' => 'automoderator-drivers',
                'id' => 'global',
                'attributes' => [
                    'action'      => $action,
                    'metric'      => $metric,
                    'requirement' => $requirement
                ],
            ],
        ]);
    }

    protected function serializeActionDrivers($drivers, $missingExt = false)
    {
        return collect($drivers)
            ->map(function (ActionDriverInterface $driver) use ($missingExt) {
                return [
                    'availableSettings' => $driver->availableSettings(),
                    'translationKey' => $driver->translationKey(),
                    'missingExt' => $missingExt
                ];
            })
            ->toArray();
    }

    protected function serializeMetricDrivers($drivers, $missingExt = false)
    {
        return collect($drivers)
            ->map(function (MetricDriverInterface $driver) use ($missingExt) {
                return [
                    'translationKey' => $driver->translationKey(),
                    'missingExt' => $missingExt
                ];
            })
            ->toArray();
    }

    protected function serializeRequirementDrivers($drivers, $missingExt = false)
    {
        return collect($drivers)
            ->map(function (RequirementDriverInterface $driver) use ($missingExt) {
                return [
                    'availableSettings' => $driver->availableSettings(),
                    'translationKey' => $driver->translationKey(),
                    'missingExt' => $missingExt
                ];
            })
            ->toArray();
    }
}
