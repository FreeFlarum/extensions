<?php

/*
 * This file is part of askvortsov/flarum-auto-moderator
 *
 *  Copyright (c) 2021 Alexander Skvortsov.
 *
 *  For detailed copyright and license information, please view the
 *  LICENSE file that was distributed with this source code.
 */

namespace Askvortsov\AutoModerator\Api\Resource;

use Askvortsov\AutoModerator\Action\ActionManager;
use Askvortsov\AutoModerator\Criterion;
use Askvortsov\AutoModerator\Metric\MetricManager;
use Askvortsov\AutoModerator\Requirement\RequirementManager;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Illuminate\Database\Eloquent\Builder;
use Tobyz\JsonApiServer\Context;

/**
 * @extends AbstractDatabaseResource<Criterion>
 */
class CriterionResource extends AbstractDatabaseResource
{
    public function __construct(protected ActionManager $actions, protected MetricManager $metrics, protected RequirementManager $requirements)
    {
    }

    public function type(): string
    {
        return 'criteria';
    }

    public function model(): string
    {
        return Criterion::class;
    }

    public function scope(Builder $query, Context $context): void
    {
        $query->whereVisibleTo($context->getActor());
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->admin(),
            Endpoint\Show::make()
                ->admin(),
            Endpoint\Create::make()
                ->admin(),
            Endpoint\Update::make()
                ->admin(),
            Endpoint\Delete::make()
                ->admin(),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('name')
                ->requiredOnCreate()
                ->writable(),
            Schema\Str::make('icon')
                ->writable(),
            Schema\Str::make('description')
                ->writable(),
            Schema\Arr::make('actions')
                ->writable(),
            Schema\Arr::make('metrics')
                ->writable(),
            Schema\Arr::make('requirements')
                ->writable(),
            Schema\Boolean::make('isValid')
                ->get(fn (Criterion $criterion) => $criterion->isValid($this->actions, $this->metrics, $this->requirements)),
            Schema\Arr::make('invalidActionSettings')
                ->get(fn (Criterion $criterion) => $criterion->invalidDriverSettings($this->actions, $criterion->actions)->messages()),
            Schema\Arr::make('invalidRequirementSettings')
                ->get(fn (Criterion $criterion) => $criterion->invalidDriverSettings($this->requirements, $criterion->requirements)->messages()),

            Schema\Relationship\ToMany::make('users')
                ->type('users')
                ->includable(),
        ];
    }

    public function saving(object $model, Context $context): ?object
    {
        $model->last_edited_by_id = $context->getActor()->id;

        return $model;
    }
}
